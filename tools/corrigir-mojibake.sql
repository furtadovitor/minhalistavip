-- =============================================================================
-- Correcao de MOJIBAKE (dupla codificacao CP850 -> UTF-8) nos dados e comentarios.
--
-- Sintoma: acentos aparecem como "Jo├úo", "Espa├ºo", "Beb├¬", "comiss├úo" etc.
-- Causa: um dump exportado por um script que capturava a saida do mysqldump
--        como TEXTO no PowerShell (decodificado em CP850/CP437) e regravava em
--        UTF-8. Ao reimportar, os bytes ficavam duplicados.
--
-- Como usar: faca BACKUP antes e rode o arquivo inteiro, ex.:
--        mysql -u USUARIO -p --default-character-set=utf8mb4 SEU_BANCO < tools/corrigir-mojibake.sql
--
-- Seguranca: os UPDATEs so alteram linhas que contenham a assinatura do mojibake
--        (o caractere '├', byte 0xE2949C). Linhas ja corretas ficam intactas, e
--        rodar de novo nao causa dano (idempotente).
-- =============================================================================

-- ---- Dados (texto) ----------------------------------------------------------
UPDATE `catalogo_presentes`  SET `nome`            = CONVERT(BINARY(CONVERT(`nome`            USING cp850)) USING utf8mb4) WHERE HEX(`nome`)            LIKE '%E2949C%';
UPDATE `catalogo_presentes`  SET `descricao`       = CONVERT(BINARY(CONVERT(`descricao`       USING cp850)) USING utf8mb4) WHERE HEX(`descricao`)       LIKE '%E2949C%';
UPDATE `categorias`          SET `nome`            = CONVERT(BINARY(CONVERT(`nome`            USING cp850)) USING utf8mb4) WHERE HEX(`nome`)            LIKE '%E2949C%';
UPDATE `configuracoes`       SET `descricao`       = CONVERT(BINARY(CONVERT(`descricao`       USING cp850)) USING utf8mb4) WHERE HEX(`descricao`)       LIKE '%E2949C%';
UPDATE `eventos`             SET `titulo`          = CONVERT(BINARY(CONVERT(`titulo`          USING cp850)) USING utf8mb4) WHERE HEX(`titulo`)          LIKE '%E2949C%';
UPDATE `eventos`             SET `descricao`       = CONVERT(BINARY(CONVERT(`descricao`       USING cp850)) USING utf8mb4) WHERE HEX(`descricao`)       LIKE '%E2949C%';
UPDATE `eventos`             SET `mensagem_convite`= CONVERT(BINARY(CONVERT(`mensagem_convite` USING cp850)) USING utf8mb4) WHERE HEX(`mensagem_convite`) LIKE '%E2949C%';
UPDATE `eventos`             SET `local_nome`      = CONVERT(BINARY(CONVERT(`local_nome`      USING cp850)) USING utf8mb4) WHERE HEX(`local_nome`)      LIKE '%E2949C%';
UPDATE `eventos`             SET `local_endereco`  = CONVERT(BINARY(CONVERT(`local_endereco`  USING cp850)) USING utf8mb4) WHERE HEX(`local_endereco`)  LIKE '%E2949C%';
UPDATE `mural_recados`       SET `mensagem`        = CONVERT(BINARY(CONVERT(`mensagem`        USING cp850)) USING utf8mb4) WHERE HEX(`mensagem`)        LIKE '%E2949C%';
UPDATE `planos`              SET `descricao`       = CONVERT(BINARY(CONVERT(`descricao`       USING cp850)) USING utf8mb4) WHERE HEX(`descricao`)       LIKE '%E2949C%';
UPDATE `presentes_evento`    SET `descricao`       = CONVERT(BINARY(CONVERT(`descricao`       USING cp850)) USING utf8mb4) WHERE HEX(`descricao`)       LIKE '%E2949C%';

-- ---- Comentarios de coluna (schema) ----------------------------------------
ALTER TABLE `carteira_movimentacoes` MODIFY COLUMN `tipo` ENUM('credito','taxa','saque','estorno','ajuste') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'credito = valor do presente; taxa = comissão da plataforma';
ALTER TABLE `eventos` MODIFY COLUMN `slug` VARCHAR(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador público do evento';
ALTER TABLE `eventos` MODIFY COLUMN `quem_paga_taxa` ENUM('convidado','organizador') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'convidado' COMMENT 'Define se a comissão é acrescida ao convidado ou descontada do organizador';
ALTER TABLE `eventos` MODIFY COLUMN `percentual_taxa` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'NULL = usa o padrão da plataforma';
ALTER TABLE `eventos` MODIFY COLUMN `limite_convidados` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Máximo de pessoas confirmadas; NULL = ilimitado';
ALTER TABLE `pedidos` MODIFY COLUMN `protocolo` VARCHAR(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código público de acompanhamento do pedido';
ALTER TABLE `planos` MODIFY COLUMN `percentual_taxa` DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Sobrescreve o percentual padrão da plataforma';
ALTER TABLE `presentes_evento` MODIFY COLUMN `catalogo_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Origem no catálogo global, quando clonado';
ALTER TABLE `webhooks_log` MODIFY COLUMN `referencia_id` VARCHAR(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'ID da transação no gateway (idempotência)';
