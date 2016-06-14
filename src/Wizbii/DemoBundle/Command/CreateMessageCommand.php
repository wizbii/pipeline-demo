<?php

namespace Wizbii\DemoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wizbii\PipelineBundle\Producer\Producer;

class CreateMessageCommand extends ContainerAwareCommand
{
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getMessageCreatedProducer()->publish(json_encode([
            "conversation_id" => $input->getOption("conversation_id"),
            "poster_id"       => $input->getOption("poster_id"),
            "message_id"      => $input->getOption("message_id"),
            "message_content" => $input->getOption("message_content"),
        ]));
    }

    protected function configure()
    {
        $this
            ->setName('demo:pipeline:message:create')
            ->addOption("conversation_id", "conversation_id", InputOption::VALUE_REQUIRED)
            ->addOption("poster_id",       "poster_id",       InputOption::VALUE_REQUIRED)
            ->addOption("message_id",      "message_id",      InputOption::VALUE_REQUIRED)
            ->addOption("message_content", "message_content", InputOption::VALUE_REQUIRED)
        ;
    }

    /**
     * @return Producer
     */
    protected function getMessageCreatedProducer()
    {
        return $this->getContainer()->get("old_sound_rabbit_mq.message_created_producer");
    }
}