<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Settings;

use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class GlossarySettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(
                [
                    'show_glossary_in_extra_tools' => '',
                ]
            )
        ;

        $allowedTypes = [
            'show_glossary_in_extra_tools' => ['string'],
        ];
        $this->setMultipleAllowedTypes($allowedTypes, $builder);
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add(
                'show_glossary_in_extra_tools',
                ChoiceType::class,
                [
                    'choices' => [
                        'None' => 'none',
                        'Exercise' => 'exercise',
                        'LearningPath' => 'lp',
                        'ExerciseAndLearningPath' => 'exercise_and_lp',
                    ],
                ]
            )
        ;
    }
}
