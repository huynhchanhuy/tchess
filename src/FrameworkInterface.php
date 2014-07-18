<?php

namespace Tchess;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Tchess\MoveManager;

interface FrameworkInterface extends HttpKernelInterface
{
    public function setEntityManager(EntityManagerInterface $entity_manager);
    public function getEntityManager();
    public function setTwig(\Twig_Environment $twig);
    public function getTwig();
    public function setFormFactory(FormFactoryInterface $form_factory);
    public function getFormFactory();
    public function setUrlGenerator(UrlGeneratorInterface $url_generator);
    public function getUrlGenerator();
    public function getEventDispatcher();
    public function setSerializer(SerializerInterface $serializer);
    public function getSerializer();
    public function setMoveManager(MoveManager $move_manager);
    public function getMoveManager();
}
