<?php


class AccountDeclarations {

    public static function GetAccountState( $Type = false ): string | array{

        $accountStates = [
            'Ready' => 1,
            'Verification'=> 2,
            'Disabled' => 3,
        ];


        if($Type != false) {
            return array_key_exists($Type, $accountStates) ? $accountStates[$Type] : $accountStates['Disabled'];
        }else{
            return $accountStates;
        }

    }

}