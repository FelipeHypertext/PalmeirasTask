<?php
namespace App\Model;

class Noticia {
    private ?int $id;
    private string $titulo;
    private string $conteudo;
    private int $criadoPor;
    private string $criadoEm;

    private function __construct() {}

    public static function criar(?int $id, string $titulo, string $conteudo, int $criadoPor, string $criadoEm = ""): static {
        $noticia = new static();
        $noticia->id        = $id;
        $noticia->criadoPor = $criadoPor;
        $noticia->criadoEm  = $criadoEm;
        $noticia->setTitulo($titulo);
        $noticia->setConteudo($conteudo);
        return $noticia;
    }

    public function getId(): ?int       { return $this->id; }
    public function getTitulo(): string  { return $this->titulo; }
    public function getConteudo(): string { return $this->conteudo; }
    public function getCriadoPor(): int  { return $this->criadoPor; }
    public function getCriadoEm(): string { return $this->criadoEm; }

    public function setTitulo(string $titulo): void {
        if ($titulo === "") {
            throw new \InvalidArgumentException("O título é obrigatório.");
        }
        $this->titulo = $titulo;
    }

    public function setConteudo(string $conteudo): void {
        if ($conteudo === "") {
            throw new \InvalidArgumentException("O conteúdo é obrigatório.");
        }
        $this->conteudo = $conteudo;
    }
}
