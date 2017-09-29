<?php
abstract class UserValidation extends DatabaseConnection
{

    private $username;
    private $userpw;

    const MAX_USERNAME_LENGTH = 25;
    const MIN_USERNAME_LENGTH = 6;

    const MAX_PASSWORD_LENGTH = 60;
    const MIN_PASSWORD_LENGTH = 6;
    
}