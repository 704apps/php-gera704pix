<?php
/*
* Biblioteca de funções para geração da linha do Pix copia e cola
* cujo texto é utilizado para a geração do QRCode para recebimento
* de pagamentos através do Pix do Banco Central.
*
* Desenvolvido em 2022 por Eduardo Roseo - https://github.com/eduardoroseo
* Inspirada no código de Renato Monteiro - https://github.com/renatomb/php_qrcode_pix
*
* Assim como o original,
* este código pode ser copiado, modificado, redistribuído
* inclusive comercialmente desde que mantida a referência ao autor.
*/

namespace Apps704\Gera704pix;

use Apps704\Gera704pix\Exceptions\TamanhoMaximoDeCaracteresException;

abstract class FuncoesPix
{
    /**
     * Esta rotina monta o código do pix conforme o padrão EMV
     * Todas as linhas são compostas por [ID do campo][Tamanho do campo com dois dígitos][Conteúdo do campo]
     * Caso o campo possua filhos esta função age de maneira recursiva.
     *
     * Autor: Eng. Renato Monteiro Batista
     * @throws TamanhoMaximoDeCaracteresException
     */
    protected function montaPix(array $px): string
    {
        $ret="";
        foreach ($px as $k => $v) {
            if (!is_array($v)) {
                if ($k == 54) {
                    $v=number_format($v,2,'.','');
                } // Formata o campo valor com 2 dígitos.
                else {
                    $v=$this->remove_char_especiais($v);
                }
                $ret.=$this->c2($k).$this->cpm($v).$v;
            }
            else {
                $conteudo=$this->montaPix($v);
                $ret.=$this->c2($k).$this->cpm($conteudo).$conteudo;
            }
        }
        return $ret;
    }

    /*
    * Esta função auxiliar calcula o CRC-16/CCITT-FALSE
    *
    * Autor: evilReiko (https://stackoverflow.com/users/134824/evilreiko)
    * Postada originalmente em: https://stackoverflow.com/questions/30035582/how-to-calculate-crc16-ccitt-in-php-hex
    */
    protected function crcChecksum(string $str): string
    {
        $crc = 0xFFFF;
        $strlen = strlen($str);
        for($c = 0; $c < $strlen; $c++) {
            $crc ^= $this->charCodeAt($str, $c) << 8;
            for($i = 0; $i < 8; $i++) {
                if($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper($hex);
        return str_pad($hex, 4, '0', STR_PAD_LEFT);
    }

    /**
    * Esta função retorna somente os caracteres alfanuméricos (a-z,A-Z,0-9) de uma string.
    * Caracteres acentuados são convertidos pelos equivalentes sem acentos.
    * Emojis são removidos, mantém espaços em branco.
    */
    private function remove_char_especiais(string $txt): string
    {
        return preg_replace('/\W /','',$this->remove_acentos($txt));
    }

    /*
    * Esta função retorna uma string substituindo os caracteres especiais de acentuação
    * pelos respectivos caracteres não acentuados em português-br.
    */
    private function remove_acentos(string $texto): string
    {
        $search = explode(",","à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż");
        $replace =explode(",","a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z");
        return $this->remove_emoji(str_replace($search, $replace, $texto));
    }

    /*
    * Esta função retorna o conteúdo de uma string removendo oas caracteres especiais
    * usados para representação de emojis.
    */
    private function remove_emoji(string $string): string
    {
        return preg_replace('%(?:
           \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
         | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
         | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        )%xs', '  ', $string);
    }

    /**
     * Esta função auxiliar retorna a quantidade de caracteres do texto $tx com dois dígitos.
     * @throws TamanhoMaximoDeCaracteresException
     */
    private function cpm($tx): string
    {
        if (strlen($tx) > 99) {
            throw new TamanhoMaximoDeCaracteresException($tx);
        }

        return $this->c2(strlen($tx));
    }

    /**
    * Esta função auxiliar trata os casos onde o tamanho do campo for < 10 acrescentando o
    * dígito 0 a esquerda.
    */
    private function c2($input): string
    {
        return str_pad($input, 2, "0", STR_PAD_LEFT);
    }

    // The PHP version of the JS str.charCodeAt(i)
    private function charCodeAt($str, $i): int
    {
        return ord(substr($str, $i, 1));
    }
}