<?php

// src/Command/CreateUserCommand.php
namespace Globalis\SeoHnTagValidator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'hN:validateWebSite',
    description: 'Run hN Validator in the whole website',
    hidden: false,
    aliases: ['hN:validateWebSite']
)]
class ValidateWebSiteCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $validator  = new \Globalis\SeoHnTagValidator\SeoHnTagValidator();

        $concurrent = 3;
        $errors = 1;

        if (!$input->getOption('only-errors')) {
            $errors = 0;
        }
        if ($input->getOption('concurrent-requests')) {
            $concurrent = $input->getOption('concurrent-requests');
        }



        $res = $validator->validateWebSite($input->getArgument('url'), $errors, $concurrent);

        //convert array errors to string
        $table = [];
        $countErrors = 0;
        for ($i = 0; $i < count($res); $i++) {
            $errors = "";
            $tags = "";
            foreach ($res[$i]["errors"] as $value) {
                $errors = $errors . $value . "\n";
                $countErrors++;
            }
            foreach ($res[$i]["tags"] as $value) {
                $tags = $tags . "<" . $value["tag"] . ">" . $value["value"] . "</" . $value["tag"] . ">" . "\n";
            }
            $table[$i]["url"] = $res[$i]["url"];
            $table[$i]["is_valid"] = $res[$i]["is_valid"];
            $table[$i]["errors"] = $errors;
            if ($tags === "") {
                $table[$i]["tags"] = "empty tags";
            } else {
                $table[$i]["tags"] = $tags;
            }
        }

        $io->table(["url","errors","is_valid","tags"], $table);

        if ($countErrors === 0) {
            $io->success($input->getArgument('url') . " crawled successfully (0 errors)");
        } else {
            $io->error($input->getArgument('url') . " crawled with " . $countErrors . " error(s)");
        }
        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to run hN validator in the whole website')
            ->addArgument('url', InputArgument::REQUIRED, 'website url')
            ->addOption('only-errors', 'e', InputOption::VALUE_OPTIONAL, "displaying only urls with errors")
            ->addOption('concurrent-requests', 'c', InputOption::VALUE_OPTIONAL)
        ;
    }
}
