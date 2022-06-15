<?php
// src/Command/CreateUserCommand.php
namespace Globalis\SeoHnTagValidator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(
    name: 'hN:validateUrl',
    description: 'Run hN Validator on a single url',
    hidden: false,
    aliases: ['hN:validateUrl']
)]
class ValidateUrlCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input,$output);
        $validator  = new \Globalis\SeoHnTagValidator\SeoHnTagValidator();



    
        $res = $validator->validateUrl($input->getArgument('url'));
        
        //convert array errors to string
        $table = [];
        $errors = "";
        $tags = "";
        foreach ($res["errors"] as $value) {
            $errors = $errors . $value . "\n";
        }
        foreach ($res["tags"] as $value) {
            $tags = $tags . "<" .$value["tag"] . ">" . $value["value"] . "</" .$value["tag"] . ">" . "\n";
        }
        $table[0]["url"] = $res["url"];
        $table[0]["errors"] = $errors;
        if($tags === "") $table[0]["tags"] = "empty tags";
        else $table[0]["tags"] = $tags;

        $io->table(["url","errors","tags"],$table);
        if($res['is_valid'] === "true")
            $io->success($input->getArgument('url')." is valid");
        else
            $io->error($input->getArgument('url')." is invalid (with ". count($res["errors"]) ." error(s))");
        return Command::SUCCESS;

    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to run hN validator on a single url')
            ->addArgument('url', InputArgument::REQUIRED, 'the desired url')
        ;
    }
}