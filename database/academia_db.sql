-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/11/2025 às 23:45
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `academia_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `treinador_id` int(11) NOT NULL,
  `data_consulta` date NOT NULL,
  `hora_consulta` time NOT NULL,
  `status` enum('agendado','confirmado','cancelado','concluido') NOT NULL DEFAULT 'agendado',
  `observacoes` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `aluno_id`, `treinador_id`, `data_consulta`, `hora_consulta`, `status`, `observacoes`, `data_criacao`, `data_atualizacao`) VALUES
(1, 12, 11, '2025-12-05', '14:30:00', 'confirmado', 'Foco em avaliação inicial e objetivos de hipertrofia.', '2025-11-23 23:39:33', '2025-11-23 23:39:33'),
(2, 13, 9, '2025-12-10', '10:00:00', 'agendado', 'Primeira consulta com Enzo Gimene.', '2025-11-23 23:39:33', '2025-11-23 23:39:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_treino`
--

CREATE TABLE `progresso_treino` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `treino_id` int(11) NOT NULL,
  `exercicio_id` int(11) NOT NULL,
  `carga` decimal(10,2) DEFAULT NULL,
  `series_realizadas` int(11) DEFAULT NULL,
  `repeticoes_realizadas` varchar(50) DEFAULT NULL,
  `data_treino` date NOT NULL,
  `observacoes` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `treinos`
--

CREATE TABLE `treinos` (
  `id` int(11) NOT NULL,
  `treinador_id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `nome_treino` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `arquivo_planilha` varchar(255) DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `treinos`
--

INSERT INTO `treinos` (`id`, `treinador_id`, `aluno_id`, `nome_treino`, `descricao`, `arquivo_planilha`, `data_criacao`, `data_atualizacao`) VALUES
(6, 9, 10, 'Treino de LPO', 'Treino com foco em Volume', NULL, '2025-11-13 17:49:21', '2025-11-13 17:49:21'),
(7, 11, 12, 'Treino de Força - Upper/Lower', 'Foco em ganhos de força nos exercícios compostos.', NULL, '2025-11-23 23:39:43', '2025-11-23 23:39:43'),
(8, 11, 12, 'Treino de Força - Upper/Lower', 'Foco em ganhos de força nos exercícios compostos.', NULL, '2025-11-23 23:41:47', '2025-11-23 23:41:47');

-- --------------------------------------------------------

--
-- Estrutura para tabela `treino_exercicios`
--

CREATE TABLE `treino_exercicios` (
  `id` int(11) NOT NULL,
  `treino_id` int(11) NOT NULL,
  `exercicio` varchar(255) NOT NULL,
  `series` int(11) DEFAULT NULL,
  `repeticoes` varchar(50) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `data_criacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `treino_exercicios`
--

INSERT INTO `treino_exercicios` (`id`, `treino_id`, `exercicio`, `series`, `repeticoes`, `descricao`, `ordem`, `data_criacao`) VALUES
(1, 6, 'Pause Back Squat', 3, '7', 'MIN RPE 6 e MAX RPE 8', 0, '2025-11-13 17:49:21'),
(2, 6, 'Pause Bench Press', 3, '8', 'MIN RPE 6 e MAX RPE 8', 1, '2025-11-13 17:49:21'),
(3, 6, 'Hang Power Snatch', 2, '3', '', 2, '2025-11-13 17:49:21'),
(4, 6, 'Jerk off Racks', 2, '4', '', 3, '2025-11-13 17:49:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `tipo_usuario` enum('aluno','treinador') NOT NULL DEFAULT 'aluno',
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `tipo_usuario`, `data_cadastro`) VALUES
(9, 'Enzo Gimene', 'enzogimeneb@gmail.com', '$2y$10$fRoLXqMn7GihG.KgqWK/OexGgX0K0ehOVYcDCCkcyhw24Sn9hQIsW', 'treinador', '2025-11-05 15:21:49'),
(10, 'Gustavo Silveira', 'gusilva@gmail.com', '$2y$10$3MVnX.hffNrnYDfQiNpuG.whPYVqa5YUdpAcG73efCSsFxYOuRPpC', 'aluno', '2025-11-05 17:31:37'),
(11, 'Juliana Mendes', 'julianamendes@email.com', '$2y$10$HASH_DA_SENHA_AQUI', 'treinador', '2025-11-23 23:39:26'),
(12, 'Pedro Rocha', 'pedro.rocha@email.com', '$2y$10$OUTRO_HASH_DA_SENHA', 'aluno', '2025-11-23 23:39:26'),
(13, 'Mariana Costa', 'mariana.costa@email.com', '$2y$10$OUTRO_HASH_DA_SENHA_2', 'aluno', '2025-11-23 23:39:26');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aluno_id` (`aluno_id`),
  ADD KEY `idx_treinador_id` (`treinador_id`),
  ADD KEY `idx_data_consulta` (`data_consulta`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `progresso_treino`
--
ALTER TABLE `progresso_treino`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aluno` (`aluno_id`),
  ADD KEY `idx_treino` (`treino_id`),
  ADD KEY `idx_exercicio` (`exercicio_id`),
  ADD KEY `idx_data_treino` (`data_treino`);

--
-- Índices de tabela `treinos`
--
ALTER TABLE `treinos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_treinador` (`treinador_id`),
  ADD KEY `idx_aluno` (`aluno_id`);

--
-- Índices de tabela `treino_exercicios`
--
ALTER TABLE `treino_exercicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_treino` (`treino_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_tipo_usuario` (`tipo_usuario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `progresso_treino`
--
ALTER TABLE `progresso_treino`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `treinos`
--
ALTER TABLE `treinos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `treino_exercicios`
--
ALTER TABLE `treino_exercicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `fk_agendamento_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_agendamento_treinador` FOREIGN KEY (`treinador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `progresso_treino`
--
ALTER TABLE `progresso_treino`
  ADD CONSTRAINT `fk_progresso_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progresso_exercicio` FOREIGN KEY (`exercicio_id`) REFERENCES `treino_exercicios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_progresso_treino` FOREIGN KEY (`treino_id`) REFERENCES `treinos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `treinos`
--
ALTER TABLE `treinos`
  ADD CONSTRAINT `fk_treino_aluno` FOREIGN KEY (`aluno_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_treino_treinador` FOREIGN KEY (`treinador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `treino_exercicios`
--
ALTER TABLE `treino_exercicios`
  ADD CONSTRAINT `fk_exercicio_treino` FOREIGN KEY (`treino_id`) REFERENCES `treinos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
