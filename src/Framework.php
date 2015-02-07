<?php

namespace Tchess;

use Symfony\Component\HttpKernel\HttpKernel;
use Tchess\FrameworkInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ValidatorInterface;

class Framework extends HttpKernel implements FrameworkInterface
{

    protected $entity_manager;
    protected $form_factory;
    protected $messageManager;
    protected $serializer;
    protected $twig;
    protected $url_generator;
    protected $validator;

    public function getEntityManager()
    {
        return $this->entity_manager;
    }

    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function getFormFactory()
    {
        return $this->form_factory;
    }

    public function getMessageManager()
    {
        return $this->messageManager;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function getTwig()
    {
        return $this->twig;
    }

    public function getUrlGenerator()
    {
        return $this->url_generator;
    }

    public function setEntityManager(EntityManagerInterface $entity_manager)
    {
        $this->entity_manager = $entity_manager;
    }

    public function setFormFactory(FormFactoryInterface $form_factory)
    {
        $this->form_factory = $form_factory;
    }

    public function setMessageManager(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function setTwig(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function setUrlGenerator(UrlGeneratorInterface $url_generator)
    {
        $this->url_generator = $url_generator;
    }

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

}
