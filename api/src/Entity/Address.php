<?php 

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A postal address.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
Class Address
{
    /**
     * @var int The id of this address.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
    
    /**
     * @var string The street address.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $streetAddress;
    
    /**
     * @var string|null The postal code of the address.
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({"read","write"})
     */
    private $postalCode;
    
    /**
     * @var string The locality of the address.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=100)
     * @Groups({"read","write"})
     */
    private $addressLocality;
    
    /**
     * @var string The country of the address.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=100)
     * @Groups({"read","write"})
     */
    private $addressCountry;
    
    /**
     * @var string The latitude of the address.
     * 
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $latitude;
    
    /**
     * @var string The longitude of the address.
     * 
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $longitude;
    
    /**
     * @var int|null The elevation of the address in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $elevation;
    
    /**
     * @var UserAddress[]|null An address may have many users.
     *
     * @ORM\OneToMany(targetEntity="UserAddress", mappedBy="address", cascade={"persist","remove"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $userAddresses;
    
    public function __construct() {
        $this->userAddresses = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getStreetAddress (): string
    {
        return $this->streetAddress;
    }

    public function getPostalCode (): ?string
    {
        return $this->postalCode;
    }

    public function getAddressLocality (): string
    {
        return $this->addressLocality;
    }

    public function getAddressCountry (): string
    {
        return $this->addressCountry;
    }

    public function getLatitude (): ?float
    {
        return $this->latitude;
    }

    public function getLongitude (): ?float
    {
        return $this->longitude;
    }

    public function getElevation (): ?int
    {
        return $this->elevation;
    }

    public function getUserAddresses ()
    {
        return $this->userAddresses;
    }

    public function setStreetAddress (string $streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }

    public function setPostalCode (?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function setAddressLocality (string $addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }

    public function setAddressCountry (string $addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    public function setLatitude (?float $latitude)
    {
        $this->latitude = $latitude;
    }

    public function setLongitude (?float $longitude)
    {
        $this->longitude = $longitude;
    }

    public function setElevation (?int $elevation)
    {
        $this->elevation = $elevation;
    }

    public function setUserAddresses (?array $userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
    public function addUserAddress(UserAddress $userAddress)
    {
        $userAddress->setAddress($this);
        $this->userAddresses->add($userAddress);
    }
    
    public function removeUserAddress(UserAddress $userAddress)
    {
        $this->userAddresses->removeElement($userAddress);
        $userAddress->setAddress(null);
    }
    
}