<?php

class RespostaApi
{
    public static function enviarSucesso($conteudoDados, $mensagem = 'Sucesso', $codigoStatus = 200)
    {
        http_response_code($codigoStatus);
        return [
            'sucesso' => true,
            'mensagem' => $mensagem,
            'dados' => $conteudoDados,
            'dataHora' => date('c')
        ];
    }

    public static function enviarErro($mensagem, $codigoStatus = 400, $erros = null)
    {
        http_response_code($codigoStatus);
        return [
            'sucesso' => false,
            'mensagem' => $mensagem,
            'erros' => $erros,
            'dataHora' => date('c')
        ];
    }

    public static function enviar($resposta)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
