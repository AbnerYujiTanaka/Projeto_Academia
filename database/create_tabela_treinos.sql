-- Script para criar tabela de treinos
-- Execute este script no phpMyAdmin ou via linha de comando

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

