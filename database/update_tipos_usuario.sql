-- Script para adicionar campo tipo_usuario e atualizar estrutura
-- Execute este script no phpMyAdmin ou via linha de comando

-- 1. Adicionar coluna tipo_usuario na tabela usuarios
ALTER TABLE `usuarios` 
ADD COLUMN `tipo_usuario` ENUM('aluno', 'treinador') NOT NULL DEFAULT 'aluno' AFTER `senha_hash`;

-- 2. Atualizar todos os usuários existentes como 'aluno' (padrão)
UPDATE `usuarios` SET `tipo_usuario` = 'aluno' WHERE `tipo_usuario` IS NULL OR `tipo_usuario` = '';

-- 3. Exemplo: Criar um usuário Treinador manualmente
-- IMPORTANTE: Substitua 'senha_segura_aqui' por uma senha forte e criptografada
-- Use password_hash() do PHP para gerar o hash da senha
-- 
-- INSERT INTO `usuarios` (`nome`, `email`, `senha_hash`, `tipo_usuario`) 
-- VALUES ('Treinador Admin', 'treinador@neongym.com', '$2y$10$EXEMPLODOHASHDA SENHASEGURA', 'treinador');

-- 4. Criar índice para melhor performance em consultas por tipo
CREATE INDEX `idx_tipo_usuario` ON `usuarios` (`tipo_usuario`);

