<?php

class User
{
    private string $id;
    private string $name;
    private string $password;
    private string $email;
    private int $groupId;

    public function getDisplayedName()
    {
        return $this->name.'<br>';
    }
}