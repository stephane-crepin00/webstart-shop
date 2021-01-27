<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\AsciiSlugger;

class BlogFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $slugger = new AsciiSlugger();

        for ($i = 0; $i < 10; $i++) {
            $post = new Post();
            $post->setTitle("Title" . $i)
                ->setSlug($slugger->slug($post->getTitle()))
                ->setCreationDate(new \DateTime())
                ->setPublished(true)
                ->setPublishedDate(new \DateTime());

            $manager->persist($post);
        }

        $manager->flush();
    }
}
