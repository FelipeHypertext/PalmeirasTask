<?php
namespace App\Model;

class Tarefa {
    private ?int $id;
    private string $titulo;
    private string $descricao;
    private string $status;
    private string $prazo;
    private int $criadoPor;
    private int $designadoPara;

    private function __construct() {}

    public static function criar(?int $id, string $titulo, string $descricao, string $status, string $prazo, int $criadoPor, int $designadoPara): static {
        $tarefa = new static();
        $tarefa->id = $id;
        $tarefa->setTitulo($titulo);
        $tarefa->descricao = $descricao;
        $tarefa->setStatus($status);
        $tarefa->setPrazo($prazo);
        $tarefa->criadoPor = $criadoPor;
        $tarefa->designadoPara = $designadoPara;
        return $tarefa;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescricao(): string { return $this->descricao; }
    public function getStatus(): string { return $this->status; }
    public function getPrazo(): string { return $this->prazo; }
    public function getCriadoPor(): int { return $this->criadoPor; }
    public function getDesignadoPara(): int { return $this->designadoPara; }

    public function setTitulo(string $titulo): void {
        if ($titulo === "") {
            throw new \InvalidArgumentException("O título é obrigatório.");
        }
        $this->titulo = $titulo;
    }

    public function setStatus(string $status): void {
        if (!in_array($status, ["pendente", "em_andamento", "concluida"])) {
            throw new \InvalidArgumentException("Status inválido.");
        }
        $this->status = $status;
    }

    public function setPrazo(string $prazo): void {
        if ($prazo === "") {
            throw new \InvalidArgumentException("O prazo é obrigatório.");
        }
        $this->prazo = $prazo;
    }
}
