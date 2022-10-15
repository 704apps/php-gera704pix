<?php

use Apps704\Gera704pix\Exceptions\CampoPixNaoInformadoException;
use Apps704\Gera704pix\GeradorPix;
use PHPUnit\Framework\TestCase;

class GerarCopiaEColaTest extends TestCase
{
    public function testGerarCopiaEColaSemChavePix()
    {
        $this->expectException(CampoPixNaoInformadoException::class);
        $this->expectExceptionMessage("Chave Pix não informada.");

        $gerarCopiaECola = new GeradorPix();
        $gerarCopiaECola->copiaECola();
    }

    public function testGerarCopiaEColaSemNomeBeneficiario()
    {
        $this->expectException(CampoPixNaoInformadoException::class);
        $this->expectExceptionMessage("Nome do beneficiário não informado.");

        $gerarCopiaECola = new GeradorPix();
        $gerarCopiaECola->setChavePix("123128731263")
            ->copiaECola();
    }

    public function testGerarCopiaEColaSemCidadePix()
    {
        $this->expectException(CampoPixNaoInformadoException::class);
        $this->expectExceptionMessage("Cidade da transação não informada.");

        $gerarCopiaECola = new GeradorPix();
        $gerarCopiaECola->setChavePix("123128731263")
            ->setNomeBeneficiario("João da Silva")
            ->copiaECola();
    }

    public function testGerarCopiaEColaSemIdentificador()
    {
        $this->expectException(CampoPixNaoInformadoException::class);
        $this->expectExceptionMessage("Identificador não informado.");

        $gerarCopiaECola = new GeradorPix();
        $gerarCopiaECola->setChavePix("123128731263")
            ->setNomeBeneficiario("João da Silva")
            ->setCidadePix("São Paulo")
            ->copiaECola();
    }

    public function testGerarCopiaEColaSemValor()
    {
        /**
         * Dados gerados em https://www.4devs.com.br/gerador_de_pessoas
         */
        $gerarCopiaECola = new GeradorPix();
        $copiaECola = $gerarCopiaECola->setChavePix("391.180.618-34")
            ->setNomeBeneficiario("Nicolas Henrique Vinicius Teixeira")
            ->setCidadePix("Fortaleza")
            ->setIdentificador("***")
            ->copiaECola();

        $this->assertIsString($copiaECola);
        $this->assertStringContainsString("391.180.618-34", $copiaECola);
        $this->assertStringContainsString("Nicolas Henrique Vinicius Teixeira", $copiaECola);
        $this->assertStringContainsString("Fortaleza", $copiaECola);
        $this->assertStringContainsString("br.gov.bcb.pix", $copiaECola);
    }

    public function testGerarCopiaEColaComValor()
    {
        /**
         * Dados gerados em https://www.4devs.com.br/gerador_de_pessoas
         */
        $gerarCopiaECola = new GeradorPix();
        $copiaECola = $gerarCopiaECola->setChavePix("391.180.618-34")
            ->setNomeBeneficiario("Nicolas Henrique Vinicius Teixeira")
            ->setCidadePix("Fortaleza")
            ->setIdentificador("***")
            ->setValorPix(100.00)
            ->copiaECola();

        $this->assertIsString($copiaECola);
        $this->assertStringContainsString("391.180.618-34", $copiaECola);
        $this->assertStringContainsString("Nicolas Henrique Vinicius Teixeira", $copiaECola);
        $this->assertStringContainsString("Fortaleza", $copiaECola);
        $this->assertStringContainsString("br.gov.bcb.pix", $copiaECola);
        $this->assertStringContainsString("100.00", $copiaECola);
    }
}