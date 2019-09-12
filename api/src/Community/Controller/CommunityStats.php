<?php


namespace App\Community\Controller;

use App\Community\Entity\Community;
use App\Community\Service\CommunityManager;
use App\TranslatorTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class CommunityStats
{
    use TranslatorTrait;
    private $communityManager;
    private $logger;

    public function __construct(CommunityManager $communityManager, LoggerInterface $logger)
    {
        $this->communityManager = $communityManager;
        $this->logger = $logger;
    }

    public function __invoke(Community $data)
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad community  id is provided"));
        }
        if ($data= $this->communityManager->getStatistics($data)) {
            return $data;
        }
        return new Response('Unauthorized', 200);
    }
}