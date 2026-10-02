<?php

use App\Services\TipoEventoService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TipoEventoServiceTest extends CIUnitTestCase
{
    public function testCatalogoTemTiposEChavesUnicas(): void
    {
        $chaves = TipoEventoService::chaves();

        $this->assertGreaterThanOrEqual(20, count($chaves));
        $this->assertSame($chaves, array_values(array_unique($chaves)));
        $this->assertContains('evento_pet', $chaves);
        $this->assertContains('casamento', $chaves);
        $this->assertContains('natal', $chaves);
    }

    public function testSlugsSaoUnicos(): void
    {
        $slugs = array_column(TipoEventoService::todos(), 'slug');

        $this->assertSame($slugs, array_values(array_unique($slugs)));
    }

    public function testPorSlugResolveOTipoDoCaminho(): void
    {
        $tipo = TipoEventoService::porSlug('evento-pet');

        $this->assertNotNull($tipo);
        $this->assertSame('evento_pet', $tipo['chave']);
        $this->assertSame('Festinha do Pet', $tipo['rotulo']);
    }

    public function testPorSlugDesconhecidoRetornaNulo(): void
    {
        $this->assertNull(TipoEventoService::porSlug('nao-existe'));
    }

    public function testRotuloUsaFallbackLegivel(): void
    {
        $this->assertSame('Chá de Bebê', TipoEventoService::rotulo('cha_bebe'));
        $this->assertSame('Tipo novo', TipoEventoService::rotulo('tipo_novo'));
    }
}
