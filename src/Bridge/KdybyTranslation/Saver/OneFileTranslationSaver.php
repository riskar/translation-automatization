<?php

namespace Efabrica\TranslationsAutomatization\Bridge\KdybyTranslation\Saver;

use Efabrica\TranslationsAutomatization\Saver\SaverInterface;
use Efabrica\TranslationsAutomatization\Tokenizer\TokenCollection;
use Nette\Neon\Encoder;
use Nette\Neon\Neon;

class OneFileTranslationSaver implements SaverInterface
{
    private $translationFile;

    public function __construct(string $translationFile)
    {
        $this->translationFile = $translationFile;
    }

    public function save(TokenCollection $tokenCollection): bool
    {
        $translations = $this->load();
        foreach ($tokenCollection->getTokens() as $token) {
            $translationKeyParts = explode('.', $token->getTranslationKey());
            array_shift($translationKeyParts);

            $translations = $this->addToTranslations($translations, $translationKeyParts, $token->getOriginalText());

//            $translations[implode('.', $translationKeyParts)] = $token->getOriginalText();
        }
        return (bool) file_put_contents($this->translationFile, str_replace("\t", "    ", Neon::encode($translations, Encoder::BLOCK)));
    }

    private function load(): array
    {
        if (!file_exists($this->translationFile)) {
            return [];
        }
        return Neon::decode(file_get_contents($this->translationFile));
    }

    private function addToTranslations(array $translations, array $translationKeyParts, string $text): array
    {
        $keyPart = array_shift($translationKeyParts);
        if (count($translationKeyParts) === 0) {
            $translations[$keyPart] = $text;
            return $translations;
        }
        if (!isset($translations[$keyPart])) {
            $translations[$keyPart] = [];
        }
        $translations[$keyPart] = $this->addToTranslations($translations[$keyPart], $translationKeyParts, $text);
        return $translations;
    }
}