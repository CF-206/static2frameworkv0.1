<?php
class Validator
{
    private array $data = [];
    private array $errors = [];
    private array $reglesAutorisees = ['required', 'email', 'min'];

    public function __construct(array $postData)
    {
        unset($postData['submit']);

        foreach ($postData as $champ => $valeur) {
            $this->data[$champ] = is_string($valeur) ? trim($valeur) : $valeur;
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function required(string $champ): void
    {
        $valeur = $this->data[$champ] ?? '';
        if ($valeur === '' || $valeur === []) {
            $this->errors[$champ] = "Le champ $champ est requis.";
        }
    }

    private function email(string $champ): void
    {
        $valeur = $this->data[$champ] ?? '';
        if ($valeur === '') {
            return; // Le champ est vide, required() gerera
        }
        if (!filter_var($valeur, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$champ] = "Le champ $champ doit être une adresse email valide.";
        }
    }

    public function validate(array $regles): bool
    {
        foreach ($regles as $champ => $listeRegle) {
            foreach ($listeRegle as $regle) {
                if (!in_array($regle, $this->reglesAutorisees)) {
                    throw new InvalidArgumentException("Règle inconnue : $regle");
                }
                $this->$regle($champ);
            }
        }
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}