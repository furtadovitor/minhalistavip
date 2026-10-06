<?php

namespace App\Services;

/**
 * Catálogo central dos tipos de evento.
 *
 * Fonte única de verdade para: rótulo exibido, ícone, tema e cores sugeridas,
 * além do slug usado na URL pública de criação (ex.: /criar-lista-de-presente/evento-pet).
 *
 * A chave de cada item é o valor gravado em `eventos.tipo_evento`.
 */
class TipoEventoService
{
    /**
     * @var array<string, array{slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}>
     */
    public const TIPOS = [
        'cha_casa_nova' => [
            'slug' => 'cha-de-casa-nova', 'rotulo' => 'Chá de Casa Nova', 'icone' => '🏡',
            'tema' => 'moderno', 'cor_primaria' => '#0EA5E9', 'cor_secundaria' => '#14B8A6',
        ],
        'cha_bebe' => [
            'slug' => 'cha-de-bebe', 'rotulo' => 'Chá de Bebê', 'icone' => '👶',
            'tema' => 'cha_bebe', 'cor_primaria' => '#06B6D4', 'cor_secundaria' => '#0891B2',
        ],
        'casamento' => [
            'slug' => 'casamento', 'rotulo' => 'Casamento', 'icone' => '💍',
            'tema' => 'casamento', 'cor_primaria' => '#D97706', 'cor_secundaria' => '#B45309',
        ],
        'aniversario' => [
            'slug' => 'aniversario', 'rotulo' => 'Aniversário', 'icone' => '🎂',
            'tema' => 'moderno', 'cor_primaria' => '#EC4899', 'cor_secundaria' => '#8B5CF6',
        ],
        'cha_panela' => [
            'slug' => 'cha-de-panela', 'rotulo' => 'Chá de Panela', 'icone' => '🍳',
            'tema' => 'classico', 'cor_primaria' => '#F97316', 'cor_secundaria' => '#EA580C',
        ],
        'cha_cozinha' => [
            'slug' => 'cha-de-cozinha', 'rotulo' => 'Chá de Cozinha', 'icone' => '🍽️',
            'tema' => 'classico', 'cor_primaria' => '#EF4444', 'cor_secundaria' => '#F59E0B',
        ],
        'noivado' => [
            'slug' => 'noivado', 'rotulo' => 'Noivado', 'icone' => '💑',
            'tema' => 'casamento', 'cor_primaria' => '#F43F5E', 'cor_secundaria' => '#FB7185',
        ],
        'cha_fraldas' => [
            'slug' => 'cha-de-fraldas', 'rotulo' => 'Chá de Fraldas', 'icone' => '🍼',
            'tema' => 'cha_bebe', 'cor_primaria' => '#38BDF8', 'cor_secundaria' => '#0EA5E9',
        ],
        'cha_revelacao' => [
            'slug' => 'cha-revelacao', 'rotulo' => 'Chá Revelação', 'icone' => '💛',
            'tema' => 'moderno', 'cor_primaria' => '#8B5CF6', 'cor_secundaria' => '#EC4899',
        ],
        'quinze_anos' => [
            'slug' => 'quinze-anos', 'rotulo' => 'Quinze Anos', 'icone' => '👑',
            'tema' => 'infantil', 'cor_primaria' => '#DB2777', 'cor_secundaria' => '#7C3AED',
        ],
        'formatura' => [
            'slug' => 'formatura', 'rotulo' => 'Formatura', 'icone' => '🎓',
            'tema' => 'classico', 'cor_primaria' => '#1D4ED8', 'cor_secundaria' => '#0F172A',
        ],
        'cha_lingerie' => [
            'slug' => 'cha-de-lingerie', 'rotulo' => 'Chá de Lingerie', 'icone' => '💕',
            'tema' => 'moderno', 'cor_primaria' => '#DB2777', 'cor_secundaria' => '#F472B6',
        ],
        'amigo_secreto' => [
            'slug' => 'amigo-secreto', 'rotulo' => 'Amigo Secreto', 'icone' => '🎅',
            'tema' => 'infantil', 'cor_primaria' => '#DC2626', 'cor_secundaria' => '#16A34A',
        ],
        'festa_infantil' => [
            'slug' => 'festa-infantil', 'rotulo' => 'Festa Infantil', 'icone' => '🎈',
            'tema' => 'infantil', 'cor_primaria' => '#F472B6', 'cor_secundaria' => '#FBBF24',
        ],
        'festa_junina' => [
            'slug' => 'festa-junina', 'rotulo' => 'Festa Junina', 'icone' => '🌽',
            'tema' => 'infantil', 'cor_primaria' => '#EA580C', 'cor_secundaria' => '#FACC15',
        ],
        'bodas' => [
            'slug' => 'bodas', 'rotulo' => 'Bodas', 'icone' => '💎',
            'tema' => 'classico', 'cor_primaria' => '#B45309', 'cor_secundaria' => '#D4AF37',
        ],
        'evento_pet' => [
            'slug' => 'evento-pet', 'rotulo' => 'Festinha do Pet', 'icone' => '🐾',
            'tema' => 'moderno', 'cor_primaria' => '#14B8A6', 'cor_secundaria' => '#F59E0B',
        ],
        'evento_igreja' => [
            'slug' => 'evento-igreja', 'rotulo' => 'Evento da Igreja', 'icone' => '⛪',
            'tema' => 'classico', 'cor_primaria' => '#4F46E5', 'cor_secundaria' => '#0EA5E9',
        ],
        'dia_namorados' => [
            'slug' => 'dia-dos-namorados', 'rotulo' => 'Dia dos Namorados', 'icone' => '❤️',
            'tema' => 'moderno', 'cor_primaria' => '#E11D48', 'cor_secundaria' => '#FB7185',
        ],
        'natal' => [
            'slug' => 'natal', 'rotulo' => 'Natal', 'icone' => '🎄',
            'tema' => 'infantil', 'cor_primaria' => '#047857', 'cor_secundaria' => '#DC2626',
        ],
        'compras' => [
            'slug' => 'compras', 'rotulo' => 'Compras', 'icone' => '🛒',
            'tema' => 'moderno', 'cor_primaria' => '#0F766E', 'cor_secundaria' => '#14B8A6',
        ],
        'material_escolar' => [
            'slug' => 'material-escolar', 'rotulo' => 'Material Escolar', 'icone' => '📚',
            'tema' => 'classico', 'cor_primaria' => '#2563EB', 'cor_secundaria' => '#F59E0B',
        ],
        'corporativo' => [
            'slug' => 'corporativo', 'rotulo' => 'Corporativo', 'icone' => '💼',
            'tema' => 'moderno', 'cor_primaria' => '#0F172A', 'cor_secundaria' => '#4F46E5',
        ],
        'outro' => [
            'slug' => 'outro', 'rotulo' => 'Outro', 'icone' => '✨',
            'tema' => 'classico', 'cor_primaria' => '#4F46E5', 'cor_secundaria' => '#10B981',
        ],
    ];

    /**
     * Atalhos exibidos na Home — 15 tipos, em grade de 3 colunas (5 linhas).
     *
     * @var list<string>
     */
    public const DESTAQUES = [
        'casamento', 'cha_bebe', 'aniversario', 'cha_panela',
        'cha_cozinha', 'cha_revelacao', 'cha_fraldas', 'quinze_anos',
        'formatura', 'noivado', 'cha_lingerie', 'festa_infantil',
        'amigo_secreto', 'festa_junina', 'natal',
    ];

    /**
     * @return list<string>
     */
    public static function chaves(): array
    {
        return array_keys(self::TIPOS);
    }

    /**
     * Tipos em destaque (15) para a grade de atalhos da Home.
     *
     * @return array<string, array{slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}>
     */
    public static function destaques(): array
    {
        $saida = [];

        foreach (self::DESTAQUES as $chave) {
            if (isset(self::TIPOS[$chave])) {
                $saida[$chave] = self::TIPOS[$chave];
            }
        }

        return $saida;
    }

    /**
     * @return array<string, array{slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}>
     */
    public static function todos(): array
    {
        return self::TIPOS;
    }

    public static function existe(string $chave): bool
    {
        return isset(self::TIPOS[$chave]);
    }

    public static function rotulo(string $chave): string
    {
        return self::TIPOS[$chave]['rotulo'] ?? ucfirst(str_replace('_', ' ', $chave));
    }

    /**
     * Dados completos do tipo, incluindo a própria chave.
     *
     * @return array{chave: string, slug: string, rotulo: string, icone: string, tema: string, cor_primaria: string, cor_secundaria: string}|null
     */
    public static function porSlug(string $slug): ?array
    {
        foreach (self::TIPOS as $chave => $dados) {
            if ($dados['slug'] === $slug) {
                return ['chave' => $chave] + $dados;
            }
        }

        return null;
    }

    public static function slugDe(string $chave): ?string
    {
        return self::TIPOS[$chave]['slug'] ?? null;
    }

    public static function urlDe(string $chave): string
    {
        return site_url('criar-lista-de-presente/' . self::slugDe($chave));
    }
}
