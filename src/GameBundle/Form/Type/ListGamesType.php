<?php

namespace LazerBall\HitTracker\GameBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class ListGamesType extends AbstractType
{
    private $repository;
    private $translator;

    public function __construct(EntityRepository $repository, TranslatorInterface $translator = null)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    private function getList()
    {
        $games = $this->repository->getRecentGames(10, 1);
        $list = [];

        foreach ($games as $game) {
            $teams = $game->getTeams();
            $transVsTeam = array_shift($teams);
            foreach ($teams as $team) {
                $transVsTeam .= ' vs. ' . $team;
            }
            $teams = $this->translator->trans('hittracker.game.list.vs_teams', [
                '%id%' => $game->getId(),
                '%vs_team%' => $transVsTeam,
            ]);

            $list[$teams] = $game->getId();
        }

        return $list;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $list = $this->getList();
        $resolver->setDefaults([
            'choices' => $list,
            'choice_translation_domain' => false,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
