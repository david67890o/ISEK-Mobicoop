<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\User\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Geography\Entity\Address;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Ask;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use App\Right\Entity\UserRole;
use App\Match\Entity\Mass;
use App\Right\Entity\UserRight;
use App\Image\Entity\Image;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\User\Controller\UserRegistration;
use App\User\Controller\UserPermissions;
use App\User\Controller\UserLogin;
use App\User\Controller\UserThreads;
use App\User\Controller\UserUpdatePassword;
use App\User\Controller\UserUpdate;
use App\User\Filter\HomeAddressTerritoryFilter;
use App\User\Filter\LoginFilter;
use App\User\Filter\PwdTokenFilter;
use App\Communication\Entity\Notified;

/**
 * A user.
 *
 * Users should not be fully removed, if a user wants to remove its account it should be anonymized, unless he has no interactions with other users.
 * Note : force eager is set to false to avoid max number of nested relations (can occure despite of maxdepth... https://github.com/api-platform/core/issues/1910)
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("email")
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","mass"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"read"}},
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/users",
 *              "controller"=UserRegistration::class,
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "normalization_context"={"groups"={"read"}},
 *          },
 *         "password_update"={
 *              "method"="PUT",
 *              "path"="/users/{id}/password_update",
 *              "controller"=UserUpdatePassword::class,
 *              "defaults"={"name"="reply"}
 *          },
 *        "password_update_request"={
 *              "method"="PUT",
 *              "path"="/users/{id}/password_update_request",
 *              "controller"=UserUpdatePassword::class,
 *              "defaults"={"name"="request"}
 *          },
 *          "permissions"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"permissions"}},
 *              "controller"=UserPermissions::class,
 *              "path"="/users/{id}/permissions",
 *              "swagger_context"={
 *                  "parameters"={
 *                      {
 *                          "name" = "territory",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "integer",
 *                          "description" = "The territory id"
 *                      },
 *                   }
 *              }
 *          },
 *          "threads"={
 *              "method"="GET",
 *              "normalization_context"={"groups"={"threads"}},
 *              "controller"=UserThreads::class,
 *              "path"="/users/{id}/threads"
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "path"="/users/{id}",
 *              "controller"=UserUpdate::class,
 *          },
 *          "delete"
 *      }
 * )
 * @ApiFilter(NumericFilter::class, properties={"id"})
 * @ApiFilter(SearchFilter::class, properties={"email":"partial", "givenName":"partial", "familyName":"partial"})
 * @ApiFilter(HomeAddressTerritoryFilter::class, properties={"homeAddressTerritory"})
 * @ApiFilter(LoginFilter::class, properties={"login"})
 * @ApiFilter(PwdTokenFilter::class, properties={"pwdToken"})
 * @ApiFilter(OrderFilter::class, properties={"id", "givenName", "familyName", "email", "gender", "nationality", "birthDate", "createdDate"}, arguments={"orderParameterName"="order"})
 */
class User implements UserInterface, EquatableInterface
{
    const MAX_DETOUR_DURATION = 600;
    const MAX_DETOUR_DISTANCE = 10000;

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 2;
    const STATUS_ANONYMIZED = 3;

    const GENDER_FEMALE = 1;
    const GENDER_MALE = 2;
    const GENDER_OTHER = 3;

    const GENDERS = [
        self::GENDER_FEMALE,
        self::GENDER_MALE,
        self::GENDER_OTHER
    ];

    /**
     * @var int The id of this user.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read", "threads", "thread"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int User status (1 = active; 2 = disabled; 3 = anonymized).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var string|null The first name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write", "threads", "thread"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write", "threads", "thread"})
     */
    private $familyName;

    /**
     * @var string The email of the user.
     *
     * @Assert\NotBlank
     * @Assert\Email()
     * @ORM\Column(type="string", length=100, unique=true)
     * @Groups({"read","write"})
     */
    private $email;

    /**
     * @var string The encoded password of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $password;

    /**
     * @var int|null The gender of the user (1=female, 2=male, 3=nc)
     *
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $gender;

    /**
     * @var string|null The nationality of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $nationality;

    /**
     * @var \DateTimeInterface|null The birth date of the user.
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"read","write"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $birthDate;

    /**
     * @var string|null The telephone number of the user.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $telephone;

    /**
     * @var int|null The maximum detour duration (in seconds) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $maxDetourDuration;

    /**
     * @var int|null The maximum detour distance (in metres) as a driver to accept a request proposal.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $maxDetourDistance;

    /**
     * @var boolean|null The user accepts any route as a passenger from its origin to the destination.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $anyRouteAsPassenger;

    /**
     * @var boolean|null The user accepts any transportation mode.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $multiTransportMode;

    /**
     * @var ArrayCollection|null A user may have many addresses.
     *
     * @ORM\OneToMany(targetEntity="\App\Geography\Entity\Address", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @ApiSubresource
     * @Groups({"read","write"})
     */
    private $addresses;

    /**
     * @var ArrayCollection|null A user may have many cars.
     *
     * @ORM\OneToMany(targetEntity="\App\User\Entity\Car", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     */
    private $cars;

    /**
     * @var ArrayCollection|null The proposals made by this user.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Proposal", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @Apisubresource
     */
    private $proposals;

    /**
     * @var ArrayCollection|null The asks made by this user.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Ask", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $asks;

    /**
     * @var ArrayCollection|null The images of the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;
    
    /**
     * @var ArrayCollection|null A user may have many roles.
     *
     * @ORM\OneToMany(targetEntity="\App\Right\Entity\UserRole", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $userRoles;

    /**
     * @var ArrayCollection|null A user may have many specific rights.
     *
     * @ORM\OneToMany(targetEntity="\App\Right\Entity\UserRight", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $userRights;

    /**
     * @var ArrayCollection|null The mass import files of the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Match\Entity\Mass", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"mass"})
     * @MaxDepth(1)
     * @ApiSubresource
     */
    private $masses;

    /**
     * @var ArrayCollection|null The messages sent by the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Message", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @ApiSubresource
     */
    private $messages;

    /**
     * @var ArrayCollection|null The messages received by the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Recipient", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     * @ApiSubresource
     */
    private $recipients;

    /**
     * @var ArrayCollection|null The notifications sent to the user.
     *
     * @ORM\OneToMany(targetEntity="\App\Communication\Entity\Notified", mappedBy="user", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $notifieds;

    /**
     * @var \DateTimeInterface Creation date of the user.
     *
     * @ORM\Column(type="datetime")
     * @Groups("read")
     */
    private $createdDate;

    /**
     * @var DateTime|null  Date of password mofification.
     *
     * @ORM\Column(type="datetime", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $pupdtime;

    /**
     * @var string|null Token for password modification..
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $pwdToken;

    /**
     * @var string|null Token for geographical search authorization.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $geoToken;

    /**
     * @var array|null The threads of the user
     * @Groups("threads")
     */
    private $threads;

    public function __construct($status = null)
    {
        $this->addresses = new ArrayCollection();
        $this->cars = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->asks = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
        $this->userRights = new ArrayCollection();
        $this->masses = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->recipients = new ArrayCollection();
        $this->notifieds = new ArrayCollection();
        if (is_null($status)) {
            $status = self::STATUS_ACTIVE;
        }
        $this->setStatus($status);
    }

    /**
     * @var array|null The permissions granted
     * @Groups("permissions")
     */
    private $permissions;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMaxDetourDuration(): ?int
    {
        return (!is_null($this->maxDetourDuration) ? $this->maxDetourDuration : self::MAX_DETOUR_DURATION);
    }

    public function setMaxDetourDuration(?int $maxDetourDuration): self
    {
        $this->maxDetourDuration = $maxDetourDuration;

        return $this;
    }

    public function getMaxDetourDistance(): ?int
    {
        return (!is_null($this->maxDetourDistance) ? $this->maxDetourDistance : self::MAX_DETOUR_DISTANCE);
    }

    public function setMaxDetourDistance(?int $maxDetourDistance): self
    {
        $this->maxDetourDistance = $maxDetourDistance;

        return $this;
    }

    public function getAnyRouteAsPassenger(): ?bool
    {
        return $this->anyRouteAsPassenger;
    }

    public function setAnyRouteAsPassenger(?bool $anyRouteAsPassenger): self
    {
        $this->anyRouteAsPassenger = $anyRouteAsPassenger;

        return $this;
    }

    public function getMultiTransportMode(): ?bool
    {
        return $this->multiTransportMode;
    }

    public function setMultiTransportMode(?bool $multiTransportMode): self
    {
        $this->multiTransportMode = $multiTransportMode;

        return $this;
    }

    public function getAddresses()
    {
        return $this->addresses->getValues();
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setUser($this);
        }

        return $this;
    }

    public function removeAddress(Address $address): self
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
            // set the owning side to null (unless already changed)
            if ($address->getUser() === $this) {
                $address->setUser(null);
            }
        }

        return $this;
    }

    public function getImages()
    {
        return $this->images->getValues();
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setUser($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getUser() === $this) {
                $image->setUser(null);
            }
        }

        return $this;
    }

    public function getCars()
    {
        return $this->cars->getValues();
    }

    public function addCar(Car $car): self
    {
        if (!$this->cars->contains($car)) {
            $this->cars->add($car);
            $car->setUser($this);
        }

        return $this;
    }

    public function removeCar(Car $car): self
    {
        if ($this->cars->contains($car)) {
            $this->cars->removeElement($car);
            // set the owning side to null (unless already changed)
            if ($car->getUser() === $this) {
                $car->setUser(null);
            }
        }

        return $this;
    }

    public function getProposals()
    {
        return $this->proposals->getValues();
    }

    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals->add($proposal);
            $proposal->setUser($this);
        }

        return $this;
    }

    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }

        return $this;
    }

    public function getAsks()
    {
        return $this->asks->getValues();
    }

    public function addAsk(Ask $ask): self
    {
        if (!$this->asks->contains($ask)) {
            $this->asks->add($ask);
            $ask->setUser($this);
        }

        return $this;
    }

    public function removeAsk(Ask $ask): self
    {
        if ($this->asks->contains($ask)) {
            $this->asks->removeElement($ask);
            // set the owning side to null (unless already changed)
            if ($ask->getUser() === $this) {
                $ask->setUser(null);
            }
        }

        return $this;
    }

    public function getUserRoles()
    {
        return $this->userRoles->getValues();
    }

    public function addUserRole(UserRole $userRole): self
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setUser($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): self
    {
        if ($this->userRoles->contains($userRole)) {
            $this->userRoles->removeElement($userRole);
            // set the owning side to null (unless already changed)
            if ($userRole->getUser() === $this) {
                $userRole->setUser(null);
            }
        }

        return $this;
    }

    public function getUserRights()
    {
        return $this->userRights->getValues();
    }
    
    public function addUserRight(UserRight $userRight): self
    {
        if (!$this->userRights->contains($userRight)) {
            $this->userRights->add($userRight);
            $userRight->setUser($this);
        }
        
        return $this;
    }
    
    public function removeUserRight(UserRight $userRight): self
    {
        if ($this->userRights->contains($userRight)) {
            $this->userRights->removeElement($userRight);
            // set the owning side to null (unless already changed)
            if ($userRight->getUser() === $this) {
                $userRight->setUser(null);
            }
        }
        
        return $this;
    }

    public function getMasses()
    {
        return $this->masses->getValues();
    }

    public function addMass(Mass $mass): self
    {
        if (!$this->masses->contains($mass)) {
            $this->masses->add($mass);
            $mass->setUser($this);
        }

        return $this;
    }

    public function removeMass(Mass $mass): self
    {
        if ($this->masses->contains($mass)) {
            $this->masses->removeElement($mass);
            // set the owning side to null (unless already changed)
            if ($mass->getUser() === $this) {
                $mass->setUser(null);
            }
        }

        return $this;
    }

    public function getMessages()
    {
        return $this->messages->getValues();
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }

    public function getRecipients()
    {
        return $this->recipients->getValues();
    }
    
    public function addRecipient(Recipient $recipient): self
    {
        if (!$this->recipients->contains($recipient)) {
            $this->recipients[] = $recipient;
            $recipient->setUser($this);
        }
        
        return $this;
    }
    
    public function removeRecipient(Recipient $recipient): self
    {
        if ($this->recipients->contains($recipient)) {
            $this->recipients->removeElement($recipient);
            // set the owning side to null (unless already changed)
            if ($recipient->getUser() === $this) {
                $recipient->setUser(null);
            }
        }
        
        return $this;
    }

    public function getNotifieds()
    {
        return $this->notifieds->getValues();
    }
    
    public function addNotified(Notified $notified): self
    {
        if (!$this->notifieds->contains($notified)) {
            $this->notifieds[] = $notified;
            $notified->setUser($this);
        }
        
        return $this;
    }
    
    public function removeNotified(Notified $notified): self
    {
        if ($this->notifieds->contains($notified)) {
            $this->notifieds->removeElement($notified);
            // set the owning side to null (unless already changed)
            if ($notified->getUser() === $this) {
                $notified->setUser(null);
            }
        }
        
        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getPupdtime()
    {
        return $this->pupdtime;
    }

    public function setPupdtime(?DateTime $pupdtime): self
    {
        $this->pupdtime = $pupdtime;
        return $this;
    }

    public function getPwdToken()
    {
        return $this->pwdToken;
    }

    public function setPwdToken(?string $pwdToken): self
    {
        $this->pwdToken = $pwdToken;
        return $this;
    }

    public function getGeoToken()
    {
        return $this->geoToken;
    }

    public function setGeoToken(?string $geoToken): self
    {
        $this->geoToken = $geoToken;
        return $this;
    }

    public function getRoles()
    {
        // we return an array of ROLE_***
        // we only return the global roles, not the territory-specific roles
        $roles = [];
        foreach ($this->userRoles as $userRole) {
            if (is_null($userRole->getTerritory())) {
                $roles[] = $userRole->getRole()->getName();
            }
        }
        return $roles;
    }


    public function getSalt()
    {
        return  null;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->email !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getThreads(): ?array
    {
        return $this->threads;
    }

    public function setThreads(array $threads): self
    {
        $this->threads = $threads;

        return $this;
    }

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }
}
