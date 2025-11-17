-- Tabela para armazenar agendamentos de consultas
-- Execute este script no phpMyAdmin ou via linha de comando

CREATE TABLE IF NOT EXISTS `agendamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aluno_id` int(11) NOT NULL,
  `treinador_id` int(11) NOT NULL,
  `data_consulta` date NOT NULL,
  `hora_consulta` time NOT NULL,
  `status` ENUM('agendado', 'confirmado', 'cancelado', 'concluido') NOT NULL DEFAULT 'agendado',
  `observacoes` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_aluno_id` (`aluno_id`),
  KEY `idx_treinador_id` (`treinador_id`),
  KEY `idx_data_consulta` (`data_consulta`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_agendamento_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agendamento_treinador` FOREIGN KEY (`treinador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

