<?php

namespace App\Command;

use App\Entity\Music;
use App\Repository\MusicRepository;
use App\Service\Metadata\MetadataService;
use App\Service\StorageService;
use App\Storage\LocalStorage;
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

    public function __construct(
        StorageService $storageService,
        MetadataService $metadataService,
        MusicRepository $musicRepository,
    ) {
        parent::__construct();

        $this->storageService = $storageService;
        $this->metadataService = $metadataService;
        $this->musicRepository = $musicRepository;
    }

    protected function configure()
    {
        $this->addArgument('path', InputArgument::OPTIONAL, 'A path inside the local music directory', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $this->storageService->buildPath(LocalStorage::LOCAL_STORAGE_PATH, $input->getArgument('path'));

        $storage = new LocalStorage;
        $files = $this->storageService->readDirectory($path);
        
        foreach ($io->progressIterate($files) as $key => $file) {
            $metadata = $this->metadataService->getMetadata($file);
            if (!$metadata) continue;

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

            $this->musicRepository->add($music, $key === array_key_last($files));
        }

        $io->success('Local music sourcing finished successfully.');

        return Command::SUCCESS;
    }
}
