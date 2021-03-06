<?php

declare(strict_types=1);

/* For licensing terms, see /license.txt */

namespace Chamilo\CoreBundle\Settings;

use Chamilo\CoreBundle\Form\Type\YesNoType;
use Sylius\Bundle\SettingsBundle\Schema\AbstractSettingsBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class SurveySettingsSchema extends AbstractSettingsSchema
{
    public function buildSettings(AbstractSettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(
                [
                    'survey_email_sender_noreply' => 'coach',
                    'extend_rights_for_coach_on_survey' => 'true',
                ]
            )
        ;
//            ->setAllowedTypes(
//                array(
//                    //'survey_email_sender_noreply' => array('string'),
//                )
//            );
    }

    public function buildForm(FormBuilderInterface $builder): void
    {
        $builder
            ->add(
                'survey_email_sender_noreply',
                ChoiceType::class,
                [
                    'choices' => [
                        'CourseCoachEmailSender' => 'coach',
                        'NoReplyEmailSender' => 'noreply',
                    ],
                ]
            )
            ->add('extend_rights_for_coach_on_survey', YesNoType::class)
        ;
    }
}
