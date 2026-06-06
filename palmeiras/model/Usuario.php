<?php
namespace App\Model;

class Usuario {
    private ?int $id;
    private string $nome;
    private string $email;
    private string $senha;
    private string $cpf;
    private string $dataNascimento;
    private string $cargo;
    private string $posicao;
    private ?string $tokenLembrar;

    private function __construct() {}

    public static function criar(
        ?int $id,
        string $nome,
        string $email,
        string $senha,
        string $cpf,
        string $dataNascimento,
        string $cargo,
        string $posicao,
        ?string $tokenLembrar = null
    ): static {
        $usuario = new static();
        $usuario->id             = $id;
        $usuario->senha          = $senha;
        $usuario->posicao        = $posicao;
        $usuario->tokenLembrar   = $tokenLembrar;
        $usuario->setNome($nome);
        $usuario->setEmail($email);
        $usuario->setCpf($cpf);
        $usuario->setDataNascimento($dataNascimento);
        $usuario->setCargo($cargo);
        return $usuario;
    }

    // Getters
    public function getId(): ?int          { return $this->id; }
    public function getNome(): string       { return $this->nome; }
    public function getEmail(): string      { return $this->email; }
    public function getSenha(): string      { return $this->senha; }
    public function getCpf(): string        { return $this->cpf; }
    public function getDataNascimento(): string { return $this->dataNascimento; }
    public function getCargo(): string      { return $this->cargo; }
    public function getPosicao(): string    { return $this->posicao; }
    public function getTokenLembrar(): ?string { return $this->tokenLembrar; }

    // Setters com validação
    public function setNome(string $nome): void {
        if ($nome === "") {
            throw new \InvalidArgumentException("O nome é obrigatório.");
        }
        $this->nome = $nome;
    }

    public function setEmail(string $email): void {
        if ($email === "") {
            throw new \InvalidArgumentException("O e-mail é obrigatório.");
        }
        $this->email = $email;
    }

    public function setCpf(string $cpf): void {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) {
            throw new \InvalidArgumentException("O CPF deve ter 11 dígitos.");
        }
        $this->cpf = $cpf;
    }

    public function setDataNascimento(string $data): void {
        if ($data === "") {
            throw new \InvalidArgumentException("A data de nascimento é obrigatória.");
        }
        $this->dataNascimento = $data;
    }

    public function setCargo(string $cargo): void {
        if (!in_array($cargo, ["admin", "membro"])) {
            throw new \InvalidArgumentException("Cargo inválido.");
        }
        $this->cargo = $cargo;
    }
}
