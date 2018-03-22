<?php


namespace FrankProjects\UltimateWarfare\DataFixtures\ORM;

//use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;

//class GameFixture extends Fixture
class GameFixture
{
    public function load(ObjectManager $manager)
    {
        return;
        $finder = new Finder();
        $finder->in('src/GameBundle/DataFixtures/SQL');
        $finder->name('*.sql');

        foreach( $finder as $file ){
            $content = $file->getContents();
            $manager->getConnection()->exec($content);
            $manager->flush();
        }
    }

    public function getOrder() {
        return 1;
    }
}
