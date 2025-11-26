# Sistema de Usuários - Alunos e Treinadores

## 📋 Visão Geral

O sistema NEON GYM possui dois tipos de usuários:

1. **Alunos** - Usuários comuns que se cadastram pelo formulário
2. **Treinadores** - Usuários administrativos criados diretamente no banco de dados

## 🚀 Configuração Inicial

### Passo 1: Atualizar o Banco de Dados

Execute o script SQL para adicionar o campo `tipo_usuario`:

```sql
-- Execute no phpMyAdmin ou via linha de comando
-- Arquivo: database/update_tipos_usuario.sql
```

Ou execute manualmente:

```sql
ALTER TABLE `usuarios` 
ADD COLUMN `tipo_usuario` ENUM('aluno', 'treinador') NOT NULL DEFAULT 'aluno' AFTER `senha_hash`;

UPDATE `usuarios` SET `tipo_usuario` = 'aluno' WHERE `tipo_usuario` IS NULL;
```

### Passo 2: Criar Treinadores

**Opção A - Via Script PHP (Recomendado):**

1. Acesse: `http://localhost/Projeto_Academia/scripts/criar_treinador.php`
2. O script criará um treinador padrão
3. **IMPORTANTE:** Altere a senha no arquivo antes de executar
4. Após criar, considere remover ou proteger o script

**Opção B - Via SQL Manual:**

```sql
-- Substitua 'SENHA_HASH_AQUI' pelo hash gerado com password_hash()
-- Use PHP para gerar: password_hash('sua_senha', PASSWORD_DEFAULT)

INSERT INTO `usuarios` (`nome`, `email`, `senha_hash`, `tipo_usuario`) 
VALUES ('Nome do Treinador', 'treinador@email.com', 'SENHA_HASH_AQUI', 'treinador');
```

**Gerar Hash de Senha em PHP:**

```php
<?php
echo password_hash('sua_senha_segura', PASSWORD_DEFAULT);
?>
```

## 📁 Estrutura de Arquivos

### Dashboards
- `public/dashboard.php` - Redireciona baseado no tipo de usuário
- `public/dashboard_aluno.php` - Dashboard para alunos
- `public/dashboard_treinador.php` - Dashboard para treinadores

### Autenticação
- `actions/processa_login.php` - Inclui `tipo_usuario` na sessão
- `actions/processa_cadastro.php` - Sempre cria como 'aluno'

### Banco de Dados
- `database/update_tipos_usuario.sql` - Script de atualização
- `scripts/criar_treinador.php` - Helper para criar treinadores

## 🔐 Sessão

Após o login, a sessão contém:

```php
$_SESSION['usuario_id']
$_SESSION['usuario_nome']
$_SESSION['usuario_email']
$_SESSION['tipo_usuario'] // 'aluno' ou 'treinador'
```

## 🎯 Funcionalidades

### Alunos (dashboard_aluno.php)
- Visualizar treinos
- Acompanhar progresso
- Agendar consultas
- Ver histórico

### Treinadores (dashboard_treinador.php)
- Gerenciar alunos
- Criar treinos
- Agendamentos

## ⚠️ Segurança

1. **Treinadores devem ser criados manualmente** - Não há formulário público
2. **Proteja o script `criar_treinador.php`** - Remova ou proteja após uso
3. **Use senhas fortes** para treinadores
4. **Valide tipo_usuario** em todas as páginas administrativas

## 🔄 Fluxo de Redirecionamento

```
Login → processa_login.php → dashboard.php
                              ↓
                    Verifica tipo_usuario
                    ↓                    ↓
            dashboard_aluno.php    dashboard_treinador.php
```

## 📝 Notas

- Usuários cadastrados pelo formulário sempre são 'aluno'
- Treinadores devem ser criados diretamente no banco
- O campo `tipo_usuario` é obrigatório e tem valor padrão 'aluno'
- Dashboards separados garantem experiência personalizada

