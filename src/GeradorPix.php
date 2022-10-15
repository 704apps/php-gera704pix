<?php

namespace Apps704\Gera704pix;

use Apps704\Gera704pix\Exceptions\CampoPixNaoInformadoException;
use Apps704\Gera704pix\Exceptions\Gera704PixException;

class GeradorPix extends FuncoesPix
{
    private $chavePix; //required
    private $nomeBeneficiario; //required
    private $cidadePix; //required
    private $identificador; //required
    private $valorPix;
    private $descricaoPix;

    public function __construct(
        string $chavePix = null,
        string $nomeBeneficiario = null,
        string $cidadePix = null,
        string $identificador = null,
        float $valorPix = 0,
        string $descricaoPix = "")
    {
        $this->chavePix = $chavePix;
        $this->nomeBeneficiario = $nomeBeneficiario;
        $this->cidadePix = $cidadePix;
        $this->identificador = $identificador;
        $this->valorPix = $valorPix;
        $this->descricaoPix = $descricaoPix;
    }

    /**
     * @throws Gera704PixException
     */
    public function copiaECola(): string
    {
        $this->validaCopiaECola();

        $px[00] = "01"; //Payload Format Indicator, Obrigatório, valor fixo: 01
        // Se o QR Code for para pagamento único (só puder ser utilizado uma vez), descomente a linha a seguir.
        //$px[01]="12"; //Se o valor 12 estiver presente, significa que o BR Code só pode ser utilizado uma vez.
        $px[26][00] = "br.gov.bcb.pix"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
        $px[26][01] = $this->chavePix;
        if (!empty($this->descricaoPix)) {
            /*
            Não é possível que a chave pix e infoAdicionais cheguem simultaneamente a seus tamanhos máximos potenciais.
            Conforme página 15 do Anexo I - Padrões para Iniciação do PIX versão 1.2.006.
            */
            $tam_max_descr = 99-(4+4+4+14+strlen($this->chavePix));
            if (strlen($this->descricaoPix) > $tam_max_descr) {
                $this->descricaoPix = substr($this->descricaoPix,0,$tam_max_descr);
            }
            $px[26][02] = $this->descricaoPix;
        }
        $px[52] = "0000"; //Merchant Category Code “0000” ou MCC ISO18245
        $px[53] = "986"; //Moeda, “986” = BRL: real brasileiro — ISO4217
        if ($this->valorPix > 0) {
            // Na versão 1.2.006 do Anexo I — Padrões para Iniciação do PIX estabelece o campo valor (54) como um campo opcional.
            $px[54] = $this->valorPix;
        }
        $px[58] = "BR"; //“BR” – Código de país ISO3166-1 alpha 2
        $px[59] = $this->nomeBeneficiario; //Nome do beneficiário/recebedor. Máximo: 25 caracteres.
        $px[60] = $this->cidadePix; //Nome cidade onde é efetuada a transação. Máximo 15 caracteres.
        $px[62][05] = $this->identificador;
        //   $px[62][50][00]="BR.GOV.BCB.BRCODE"; //Payment system specific template - GUI
        //   $px[62][50][01]="1.2.006"; //Payment system specific template - versão
        $pix = $this->montaPix($px);
        $pix .= "6304"; //Adiciona o campo do CRC no fim da linha do pix.
        $pix .= $this->crcChecksum($pix); //Calcula o checksum CRC16 e acrescenta ao final.

        return $pix;
    }

    /**
     * @param string $chavePix
     * @return GeradorPix
     */
    public function setChavePix(string $chavePix): GeradorPix
    {
        $this->chavePix = $chavePix;
        return $this;
    }

    /**
     * @param string $nomeBeneficiario
     * @return GeradorPix
     */
    public function setNomeBeneficiario(string $nomeBeneficiario): GeradorPix
    {
        $this->nomeBeneficiario = $nomeBeneficiario;
        return $this;
    }

    /**
     * @param string $cidadePix
     * @return GeradorPix
     */
    public function setCidadePix(string $cidadePix): GeradorPix
    {
        $this->cidadePix = $cidadePix;
        return $this;
    }

    /**
     * @param string $identificador
     * @return GeradorPix
     */
    public function setIdentificador(string $identificador): GeradorPix
    {
        $this->identificador = $identificador;
        return $this;
    }

    /**
     * @param float $valorPix
     * @return GeradorPix
     */
    public function setValorPix(float $valorPix): GeradorPix
    {
        $this->valorPix = $valorPix;
        return $this;
    }

    /**
     * @param string $descricaoPix
     * @return GeradorPix
     */
    public function setDescricaoPix(string $descricaoPix): GeradorPix
    {
        $this->descricaoPix = $descricaoPix;
        return $this;
    }

    /**
     * @throws CampoPixNaoInformadoException
     */
    private function validaCopiaECola(): void
    {
        if (empty($this->chavePix)) {
            throw new CampoPixNaoInformadoException("Chave Pix não informada.");
        }
        if (empty($this->nomeBeneficiario)) {
            throw new CampoPixNaoInformadoException("Nome do beneficiário não informado.");
        }
        if (empty($this->cidadePix)) {
            throw new CampoPixNaoInformadoException("Cidade da transação não informada.");
        }
        if (empty($this->identificador)) {
            throw new CampoPixNaoInformadoException("Identificador não informado.");
        }
    }
}