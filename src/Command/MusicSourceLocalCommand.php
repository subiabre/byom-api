<?php

namespace App\Command;

use App\Entity\Music;
use App\Repository\MusicRepository;
use App\Service\Metadata\MetadataService;
use App\Service\StorageService;
use App\Storage\LocalStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'music:source:local',
    description: 'Imports music from the local music directory',
)]
class MusicSourceLocalCommand extends Command
{
    private StorageService $storageService;
    private MetadataService $metadataService;
    private MusicRepository $musicRepository;
    private EntityManagerInterface $em;

    public function __construct(
        StorageService $storageService,
        MetadataService $metadataService,
        MusicRepository $musicRepository,
        EntityManagerInterface $entityManagerInterface
    ) {
        parent::__construct();

        $this->storageService = $storageService;
        $this->metadataService = $metadataService;
        $this->musicRepository = $musicRepository;
        $this->em = $entityManagerInterface;
    }

    protected function configure()
    {
        $this->addArgument('path', InputArgument::OPTIONAL, 'A path inside the local music directory', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $files = $this->storageService->readDirectory($input->getArgument('path'));

        foreach ($io->progressIterate($files) as $key => $file) {
            $metadata = $this->metadataService->getMetadata($file);
            if (!$metadata) continue;

            $storage = new LocalStorage;
            $storage->setPath($file);

            $music = $this->musicRepository->findOneByHash($storage->getHash());
            if (!$music) $music = new Music;

            $music->setStorage($storage);
            $music->setHash($storage->getHash());
            $music->setTitle($metadata->getTitle());
            $music->setAlbum($metadata->getAlbum());
            $music->setArtist($metadata->getArtist());
            $music->setMetadata($metadata->getExtra());
            $music->setPicture($metadata->getPicture());

            $this->em->persist($music);
        }

        $this->em->flush();

        $io->success('Local music sourcing finished successfully.');

        return Command::SUCCESS;
    }
}
