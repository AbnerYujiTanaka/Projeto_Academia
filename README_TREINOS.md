# Sistema de Envio de Treinos

## 📋 Visão Geral

Sistema que permite aos treinadores enviar planilhas de treino personalizadas para os alunos da academia.

## 🚀 Configuração Inicial

### Passo 1: Criar Tabela no Banco de Dados

Execute o script SQL para criar a tabela de treinos:

```sql
-- Arquivo: database/create_tabela_treinos.sql
```

Ou execute manualmente no phpMyAdmin:

```sql
CREATE TABLE IF NOT EXISTS `treinos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `treinador_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `nome_treino` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `arquivo_planilha` varchar(255) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_treinador` (`treinador_id`),
  KEY `idx_aluno` (`aluno_id`),
  CONSTRAINT `fk_treino_treinador` FOREIGN KEY (`treinador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_treino_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

**IMPORTANTE:** Certifique-se de que a tabela `usuarios` já possui o campo `tipo_usuario` antes de executar este script (veja `database/update_tipos_usuario.sql`).

### Passo 2: Verificar Permissões do Diretório

O diretório `uploads/treinos/` deve ter permissões de escrita. No Windows/XAMPP, geralmente já está configurado corretamente.

## 📁 Estrutura de Arquivos

### Páginas Criadas

- `public/criar_treino.php` - Página para treinador criar e enviar treinos
- `public/ver_treinos.php` - Página para aluno visualizar treinos recebidos
- `public/download_treino.php` - Script para download seguro de planilhas

### Diretórios

- `uploads/treinos/` - Armazena as planilhas enviadas pelos treinadores

### Banco de Dados

- `database/create_tabela_treinos.sql` - Script de criação da tabela

## 🎯 Funcionalidades

### Para Treinadores

1. **Criar Treino** (`criar_treino.php`)
   - Selecionar aluno da lista
   - Definir nome do treino
   - Adicionar descrição (opcional)
   - Enviar planilha (PDF, Excel, Word)
   - Visualizar mensagens de sucesso/erro

### Para Alunos

1. **Visualizar Treinos** (`ver_treinos.php`)
   - Ver todos os treinos recebidos
   - Informações do treinador
   - Data de criação e atualização
   - Descrição do treino
   - Download da planilha

## 📝 Formatos de Arquivo Aceitos

- PDF (`.pdf`)
- Excel (`.xlsx`, `.xls`)
- Word (`.doc`, `.docx`)

**Tamanho máximo:** 10MB (pode ser ajustado no PHP)

## 🔐 Segurança

1. **Validação de Permissões**
   - Treinadores só podem criar treinos
   - Alunos só podem ver seus próprios treinos
   - Download verifica permissões antes de servir arquivo

2. **Proteção de Uploads**
   - Arquivos PHP são bloqueados no diretório de uploads
   - Nomes de arquivo são gerados com `uniqid()` para evitar conflitos
   - Validação de extensões permitidas

3. **Sanitização**
   - Todos os dados de entrada são sanitizados
   - Uso de `htmlspecialchars()` para prevenir XSS
   - Prepared statements para prevenir SQL Injection

## 🔄 Fluxo de Uso

```
Treinador:
1. Acessa Dashboard → Clica em "Criar Treinos"
2. Seleciona aluno, preenche dados e envia planilha
3. Sistema salva no banco e armazena arquivo

Aluno:
1. Acessa Dashboard → Clica em "Meus Treinos"
2. Visualiza lista de treinos recebidos
3. Clica em "Baixar Planilha" para download
```

## ⚠️ Notas Importantes

1. **Backup Regular**: Faça backup regular do diretório `uploads/treinos/`
2. **Limpeza**: Considere implementar limpeza automática de arquivos antigos
3. **Espaço em Disco**: Monitore o uso de espaço em disco
4. **Validação**: O sistema valida extensões, mas sempre verifique arquivos suspeitos

## 🐛 Solução de Problemas

### Erro ao fazer upload
- Verifique permissões do diretório `uploads/treinos/`
- Verifique `upload_max_filesize` e `post_max_size` no `php.ini`

### Erro ao criar treino
- Verifique se a tabela `treinos` foi criada corretamente
- Verifique se as foreign keys estão funcionando
- Verifique se os IDs de usuário existem

### Arquivo não aparece para download
- Verifique se o arquivo foi salvo corretamente em `uploads/treinos/`
- Verifique permissões de leitura do arquivo








