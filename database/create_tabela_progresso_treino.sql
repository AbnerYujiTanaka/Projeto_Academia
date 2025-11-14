-- Script para criar tabela de progresso de treinos
-- Execute este script no phpMyAdmin ou via linha de comando

CREATE TABLE IF NOT EXISTS `progresso_treino` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aluno_id` int(11) NOT NULL,
  `treino_id` int(11) NOT NULL,
  `exercicio_id` int(11) NOT NULL,
  `carga` decimal(10,2) DEFAULT NULL,
  `series_realizadas` int(11) DEFAULT NULL,
  `repeticoes_realizadas` varchar(50) DEFAULT NULL,
  `data_treino` date NOT NULL,
  `observacoes` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_aluno` (`aluno_id`),
  KEY `idx_treino` (`treino_id`),
  KEY `idx_exercicio` (`exercicio_id`),
  KEY `idx_data_treino` (`data_treino`),
  CONSTRAINT `fk_progresso_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progresso_treino` FOREIGN KEY (`treino_id`) REFERENCES `treinos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_progresso_exercicio` FOREIGN KEY (`exercicio_id`) REFERENCES `treino_exercicios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

