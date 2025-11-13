-- Script para criar tabela de exercícios dos treinos
-- Execute este script no phpMyAdmin ou via linha de comando

CREATE TABLE IF NOT EXISTS `treino_exercicios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `treino_id` int(11) NOT NULL,
  `exercicio` varchar(255) NOT NULL,
  `series` int(11) DEFAULT NULL,
  `repeticoes` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `data_criacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_treino` (`treino_id`),
  CONSTRAINT `fk_exercicio_treino` FOREIGN KEY (`treino_id`) REFERENCES `treinos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

