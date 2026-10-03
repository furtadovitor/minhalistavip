<?php

namespace App\Services;

/**
 * Catálogo de modelos visuais do hotsite (aparência do evento).
 *
 * Cada modelo é uma combinação de tipografia (fonte de título/corpo e raio dos
 * cantos) + paleta de cores sugerida. A chave é gravada em `eventos.tema` e lida
 * por `templates/partials/design_evento.php`.
 *
 * A ordem define a exibição no seletor: os 6 primeiros são os "principais".
 */
class ModeloService
{
    /**
     * @var array<string, array{rotulo: string, icone: string, cor_primaria: string, cor_secundaria: string}>
     */
    public const MODELOS = [
        'classico' => [
            'rotulo' => 'Clássico', 'icone' => '✦',
            'cor_primaria' => '#4F46E5', 'cor_secundaria' => '#10B981',
        ],
        'casamento' => [
            'rotulo' => 'Casamento', 'icone' => '💍',
            'cor_primaria' => '#D97706', 'cor_secundaria' => '#B45309',
        ],
        'cha_bebe' => [
            'rotulo' => 'Chá de Bebê', 'icone' => '👶',
            'cor_primaria' => '#06B6D4', 'cor_secundaria' => '#0891B2',
        ],
        'moderno' => [
            'rotulo' => 'Moderno', 'icone' => '⚡',
            'cor_primaria' => '#EC4899', 'cor_secundaria' => '#8B5CF6',
        ],
        'infantil' => [
            'rotulo' => 'Infantil', 'icone' => '🎈',
            'cor_primaria' => '#F472B6', 'cor_secundaria' => '#FBBF24',
        ],
        'elegante' => [
            'rotulo' => 'Elegante', 'icone' => '🖤',
            'cor_primaria' => '#0F172A', 'cor_secundaria' => '#C8A24A',
        ],
        'romantico' => [
            'rotulo' => 'Romântico', 'icone' => '🌸',
            'cor_primaria' => '#E11D48', 'cor_secundaria' => '#FB7185',
        ],
        'minimalista' => [
            'rotulo' => 'Minimalista', 'icone' => '◻️',
            'cor_primaria' => '#374151', 'cor_secundaria' => '#9CA3AF',
        ],
        'tropical' => [
            'rotulo' => 'Tropical', 'icone' => '🌴',
            'cor_primaria' => '#0D9488', 'cor_secundaria' => '#EA580C',
        ],
        'rustico' => [
            'rotulo' => 'Rústico', 'icone' => '🌾',
            'cor_primaria' => '#4D7C0F', 'cor_secundaria' => '#92400E',
        ],
        'festivo' => [
            'rotulo' => 'Festivo', 'icone' => '🎉',
            'cor_primaria' => '#7C3AED', 'cor_secundaria' => '#F59E0B',
        ],
        'luxo' => [
            'rotulo' => 'Luxo', 'icone' => '👑',
            'cor_primaria' => '#111827', 'cor_secundaria' => '#D4AF37',
        ],
    ];

    /**
     * Tipografia e formato dos cantos por modelo (usada no design do hotsite).
     *
     * @var array<string, array{titulo: string, corpo: string, google: string, raio: string}>
     */
    public const TIPOGRAFIA = [
        'classico'    => ['titulo' => 'Playfair Display', 'corpo' => 'Inter', 'google' => 'Playfair+Display:wght@600;700;800&family=Inter:wght@400;500;600', 'raio' => '1.25rem'],
        'casamento'   => ['titulo' => 'Cormorant Garamond', 'corpo' => 'Inter', 'google' => 'Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600', 'raio' => '1.1rem'],
        'cha_bebe'    => ['titulo' => 'Poppins', 'corpo' => 'Nunito', 'google' => 'Poppins:wght@600;700;800&family=Nunito:wght@400;600', 'raio' => '1.75rem'],
        'moderno'     => ['titulo' => 'Space Grotesk', 'corpo' => 'Inter', 'google' => 'Space+Grotesk:wght@600;700&family=Inter:wght@400;500;600', 'raio' => '.75rem'],
        'infantil'    => ['titulo' => 'Baloo 2', 'corpo' => 'Nunito', 'google' => 'Baloo+2:wght@600;700;800&family=Nunito:wght@400;600', 'raio' => '1.9rem'],
        'elegante'    => ['titulo' => 'Marcellus', 'corpo' => 'Jost', 'google' => 'Marcellus&family=Jost:wght@400;500;600', 'raio' => '.5rem'],
        'romantico'   => ['titulo' => 'Great Vibes', 'corpo' => 'Poppins', 'google' => 'Great+Vibes&family=Poppins:wght@400;500;600', 'raio' => '1.4rem'],
        'minimalista' => ['titulo' => 'DM Sans', 'corpo' => 'DM Sans', 'google' => 'DM+Sans:wght@400;500;700', 'raio' => '.5rem'],
        'tropical'    => ['titulo' => 'Pacifico', 'corpo' => 'Nunito Sans', 'google' => 'Pacifico&family=Nunito+Sans:wght@400;600;700', 'raio' => '1.4rem'],
        'rustico'     => ['titulo' => 'Amatic SC', 'corpo' => 'Karla', 'google' => 'Amatic+SC:wght@700&family=Karla:wght@400;500;600', 'raio' => '.9rem'],
        'festivo'     => ['titulo' => 'Righteous', 'corpo' => 'Rubik', 'google' => 'Righteous&family=Rubik:wght@400;500;600', 'raio' => '1.2rem'],
        'luxo'        => ['titulo' => 'Cinzel', 'corpo' => 'Montserrat', 'google' => 'Cinzel:wght@600;700&family=Montserrat:wght@400;500;600', 'raio' => '.4rem'],
    ];

    /**
     * @return array<string, array{rotulo: string, icone: string, cor_primaria: string, cor_secundaria: string}>
     */
    public static function todos(): array
    {
        return self::MODELOS;
    }

    public static function existe(string $chave): bool
    {
        return isset(self::MODELOS[$chave]);
    }

    public static function rotulo(string $chave): string
    {
        return self::MODELOS[$chave]['rotulo'] ?? ucfirst(str_replace('_', ' ', $chave));
    }

    /**
     * Modelo completo (com a própria chave) ou null.
     *
     * @return array{chave: string, rotulo: string, icone: string, cor_primaria: string, cor_secundaria: string}|null
     */
    public static function um(string $chave): ?array
    {
        if (! isset(self::MODELOS[$chave])) {
            return null;
        }

        return ['chave' => $chave] + self::MODELOS[$chave];
    }

    /**
     * Apara os dados de tipografia/cores de um modelo, com fallback no clássico.
     *
     * @return array{titulo: string, corpo: string, google: string, raio: string}
     */
    public static function tipografia(string $chave): array
    {
        return self::TIPOGRAFIA[$chave] ?? self::TIPOGRAFIA['classico'];
    }

    /**
     * Rótulos para selects (chave => nome).
     *
     * @return array<string, string>
     */
    public static function rotulos(): array
    {
        $rotulos = [];

        foreach (self::MODELOS as $chave => $modelo) {
            $rotulos[$chave] = $modelo['rotulo'];
        }

        return $rotulos;
    }

    /**
     * URL única do Google Fonts com a fonte de título de TODOS os modelos
     * (usada só na tela de escolha, para pré-visualizar).
     */
    public static function googleFontsUrl(): string
    {
        $familias = [];

        foreach (self::MODELOS as $chave => $modelo) {
            $google = self::tipografia($chave)['google'];
            // Só a família de título (a primeira do par) para a prévia.
            $familias[] = explode('&', $google)[0];
        }

        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', array_unique($familias)) . '&display=swap';
    }
}
