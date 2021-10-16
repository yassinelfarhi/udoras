<?php

namespace FS\TrainingProgramsBundle\Util;


use Symfony\Component\Validator\Validator\ValidatorInterface;


class EmailVerifier
{
    /**
     * @param array $emails
     * @param ValidatorInterface $validator
     * @return array
     */
    public static function filterValidEmails($emails, ValidatorInterface $validator)
    {
        $countedEmails = array_count_values($emails);
        $constrains = [
            new \Symfony\Component\Validator\Constraints\Email(),
            new \Symfony\Component\Validator\Constraints\NotBlank()
        ];

        return array_filter($emails, function ($email) use ($validator, $constrains, $countedEmails) {
            return count($validator->validateValue($email, $constrains)) == 0 && $countedEmails[$email] == 1;
        });
    }
}