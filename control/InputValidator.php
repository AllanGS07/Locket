<?php

class InputValidator
{
    public static function sanitizeString($value)
    {
        if (is_null($value)) {
            return null;
        }
        return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
    
    public static function sanitizeEmail($email)
    {
        $email = trim($email);
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email inválido');
        }
        return strtolower($email);
    }
    
    public static function sanitizeCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) !== 11) {
            throw new Exception('CPF deve conter 11 dígitos');
        }
        
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            throw new Exception('CPF inválido');
        }
        
        return $cpf;
    }
    
    public static function validatePassword($password)
    {
        if (strlen($password) < 8) {
            throw new Exception('Senha deve ter no mínimo 8 caracteres');
        }
        
        if (! preg_match('/[A-Z]/', $password)) {
            throw new Exception('Senha deve conter pelo menos uma letra maiúscula');
        }
        
        if (! preg_match('/[a-z]/', $password)) {
            throw new Exception('Senha deve conter pelo menos uma letra minúscula');
        }
        
        if (! preg_match('/[0-9]/', $password)) {
            throw new Exception('Senha deve conter pelo menos um número');
        }
        
        if (! preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            throw new Exception('Senha deve conter pelo menos um caractere especial');
        }
        
        return true;
    }
    
    public static function sanitizeInteger($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        if ($value === false) {
            throw new Exception('Valor deve ser um número inteiro');
        }
        return $value;
    }
    
    public static function sanitizeDate($date)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        if ($dateTime === false) {
            throw new Exception('Data deve estar no formato YYYY-MM-DD');
        }
        return $dateTime->format('Y-m-d');
    }
    
    public static function validateEnum($value, $validValues)
    {
        if (! in_array($value, $validValues, true)) {
            throw new Exception('Valor inválido. Valores aceitos: ' . implode(', ', $validValues));
        }
        return $value;
    }
}
