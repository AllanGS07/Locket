<?php

class ValidadorEntrada
{
    public static function desinfectarTexto($valor)
    {
        if (is_null($valor)) {
            return null;
        }
        return trim(htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'));
    }

    public static function desinfectarEmail($email)
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email invalido');
        }
        return strtolower($email);
    }

    public static function desinfectarCPF($cpf)
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) !== 11) {
            throw new Exception('CPF deve conter 11 digitos');
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            throw new Exception('CPF invalido');
        }

        return $cpf;
    }

    public static function validarSegurancaSenha($senha)
    {
        if (strlen($senha) < 8) {
            throw new Exception('Senha deve ter no minimo 8 caracteres');
        }

        if (!preg_match('/[A-Z]/', $senha)) {
            throw new Exception('Senha deve conter pelo menos uma letra maiuscula');
        }

        if (!preg_match('/[a-z]/', $senha)) {
            throw new Exception('Senha deve conter pelo menos uma letra minuscula');
        }

        if (!preg_match('/[0-9]/', $senha)) {
            throw new Exception('Senha deve conter pelo menos um numero');
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $senha)) {
            throw new Exception('Senha deve conter pelo menos um caractere especial');
        }

        return true;
    }

    public static function desinfectarNumeroInteiro($valor)
    {
        $valor = filter_var($valor, FILTER_VALIDATE_INT);
        if ($valor === false) {
            throw new Exception('Valor deve ser um numero inteiro');
        }
        return $valor;
    }

    public static function desinfectarData($data)
    {
        $dataHoraObjeto = DateTime::createFromFormat('Y-m-d', $data);
        if ($dataHoraObjeto === false) {
            throw new Exception('Data deve estar no formato YYYY-MM-DD');
        }
        return $dataHoraObjeto->format('Y-m-d');
    }

    public static function validarValorEstaEmEnumeracao($valor, $valoresValidos)
    {
        if (!in_array($valor, $valoresValidos, true)) {
            throw new Exception('Valor invalido. Valores aceitos: ' . implode(', ', $valoresValidos));
        }
        return $valor;
    }
}
