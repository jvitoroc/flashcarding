<?php
class ErrorCodes
{
    static private $errorCodeMessages = array(
    	'101' => array(
    		'pt' => 'As senhas não são iguais!',
    		'en' => 'The given passwords are not equal!'
    	),
    	'102' => array(
    		'pt' => 'O nome de usuário deve ter de 6 à 25 caracteres!',
    		'en' => 'The username must have from 6 to 25 characters!'
    	),
    	'103' => array(
    		'pt' => 'A senha deve ter de 6 à 25 caracteres!',
    		'en' => 'The password must have from 6 to 25 characters!'
    	),
    	'104' => array(
    		'pt' => 'A nome deve ter de 12 à 70 caracteres!',
    		'en' => 'The name must have from 6 to 70 characters!'
    	),
    	'201' => array(
    		'pt' => 'Já existe alguém com esse nome de usuário!',
    		'en' => 'There is someone with this username!'
    	),
    	'202' => array(
    		'pt' => 'Ocorreu um erro ao criar sua conta, tente mais tarde!',
    		'en' => 'Try again later!'
    	),
    	'200' => array(
    		'pt' => 'Registrado com sucesso!',
    		'en' => 'Successfully registered!'
    	),
        '210' => array(
            'pt' => 'Logado com sucesso!',
            'en' => 'Your session successfully started!'
        ),
        '211' => array(
            'pt' => 'Senha ou usuário inválidos!',
            'en' => 'Username or password invalid!'
        ),
        '212' => array(
            'pt' => 'Ocorreu um erro, tente mais tarde!',
            'en' => 'Try again later!'
        ),
        '301' => array(
            'pt' => 'Você não tem flashcards!',
            'en' => 'You do not have flashcards!'
        ),
        '302' => array(
            'pt' => 'Ocorreu um erro ao tentar carregar os seus flashcards',
            'en' => 'An error occurred trying to get you flashcards!'
        )
    );

    static function getErrorMessage($errorCode, $lang){
        if(is_array($errorCode)){
            $errorMessages = array();
            foreach($errorCode as $code){
                $errorMessages[] = self::$errorCodeMessages[$code][$lang];
            }
            return $errorMessages;
        }else{
            return array(self::$errorCodeMessages[$errorCode][$lang]);
        }
    }

}