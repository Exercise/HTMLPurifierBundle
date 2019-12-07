<?php

namespace Exercise\HTMLPurifierBundle\Form\Listener;

use Exercise\HTMLPurifierBundle\HTMLPurifiersRegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HTMLPurifierListener implements EventSubscriberInterface
{
    private $registry;
    private $profile;

    public function __construct(HTMLPurifiersRegistryInterface $registry, string $profile)
    {
        $this->registry = $registry;
        $this->profile = $profile;
    }

    public function purifySubmittedData(FormEvent $event): void
    {
        if (!is_scalar($data = $event->getData())) {
            // Hope there is a view transformer, otherwise an error might happen
            return; // because we don't want to handle it here
        }

        if (0 === strlen($submittedData = trim($data))) {
            if ($submittedData !== $data) {
                $event->setData($submittedData);
            }

            return;
        }

        $event->setData($this->getPurifier()->purify($submittedData));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => ['purifySubmittedData', /* as soon as possible */ 1000000],
        ];
    }

    private function getPurifier(): \HTMLPurifier
    {
        return $this->registry->get($this->profile);
    }
}
