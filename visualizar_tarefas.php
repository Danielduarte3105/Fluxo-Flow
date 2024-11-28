<?php
    session_start();
    include 'log_atividade.php';

    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }

    $tasksFile = 'json/tasks.json';
    $tasks = json_decode(file_get_contents($tasksFile), true) ?: [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desarquivar_id'])) {
        // Desarquivar a tarefa
        $taskId = $_POST['desarquivar_id'];
        foreach ($tasks as &$task) {
            if ($task['id'] == $taskId && $task['status'] === 'arquivada') {
                $task['status'] = 'pendente'; // Mudar o status para pendente
                break;
            }
        }

        // Salvar as alterações no arquivo JSON
        file_put_contents($tasksFile, json_encode($tasks, JSON_PRETTY_PRINT));

        // Redirecionar para a página inicial
        $_SESSION['mensagem'] = 'Tarefa desarquivada com sucesso!';
        header('Location: index.php');
        exit;
    }

    include "require/header.php";
    include "require/aside.php";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Tarefas</title>
    <!-- Inclusão do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .task-table th, .task-table td {
            text-align: center;
        }
        .task-table .btn {
            min-width: 120px;
        }
        .alert {
            margin-top: 20px;
        }
        .task-section {
            display: none; /* Esconde as seções por padrão */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Tarefas</h2>

        <!-- Mensagem de Sucesso -->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?>
            </div>
        <?php endif; ?>

        <!-- Filtros de Status -->
        <div class="mb-3">
            <button class="btn btn-primary me-2" onclick="showTasks('pendentes')">Tarefas Pendentes</button>
            <button class="btn btn-success me-2" onclick="showTasks('respondidas')">Tarefas Respondidas</button>
            <button class="btn btn-secondary me-2" onclick="showTasks('arquivadas')">Tarefas Arquivadas</button>
            <button class="btn btn-danger" onclick="showTasks('encerradas')">Tarefas Encerradas</button>
        </div>

        <!-- Tarefas Pendentes -->
        <section id="pendentes" class="task-section">
            <h3 class="text-primary">Tarefas Pendentes</h3>
            <?php
            $pendentes = array_filter($tasks, function($task) {
                return $task['status'] === 'pendente';
            });

            if (!empty($pendentes)): ?>
                <table class="table table-bordered table-striped task-table">
                    <thead class="table-primary">
                        <tr>
                            <th>ID da Tarefa</th>
                            <th>Usuário</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Data de Designação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendentes as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><?php echo htmlspecialchars($task['responsavel']); ?></td>
                                <td><?php echo htmlspecialchars($task['descricao']); ?></td>
                                <td><?php echo ucfirst($task['status']); ?></td>
                                <td><?php echo $task['data_designacao']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Não há tarefas pendentes no momento.</p>
            <?php endif; ?>
        </section>

        <!-- Tarefas Respondidas -->
        <section id="respondidas" class="task-section">
            <h3 class="text-success mt-5">Tarefas Respondidas</h3>
            <?php
            $respondidas = array_filter($tasks, function($task) {
                return $task['status'] === 'respondida';
            });

            if (!empty($respondidas)): ?>
                <table class="table table-bordered table-striped task-table">
                    <thead class="table-success">
                        <tr>
                            <th>ID da Tarefa</th>
                            <th>Usuário</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Data de Designação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($respondidas as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><?php echo htmlspecialchars($task['responsavel']); ?></td>
                                <td><?php echo htmlspecialchars($task['descricao']); ?></td>
                                <td><?php echo ucfirst($task['status']); ?></td>
                                <td><?php echo $task['data_designacao']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Não há tarefas respondidas no momento.</p>
            <?php endif; ?>
        </section>

        <!-- Tarefas Arquivadas -->
        <section id="arquivadas" class="task-section">
            <h3 class="text-secondary mt-5">Tarefas Arquivadas</h3>
            <?php
            $arquivadas = array_filter($tasks, function($task) {
                return $task['status'] === 'arquivada';
            });

            if (!empty($arquivadas)): ?>
                <table class="table table-bordered table-striped task-table">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID da Tarefa</th>
                            <th>Usuário</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Data de Designação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($arquivadas as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><?php echo htmlspecialchars($task['responsavel']); ?></td>
                                <td><?php echo htmlspecialchars($task['descricao']); ?></td>
                                <td><?php echo ucfirst($task['status']); ?></td>
                                <td><?php echo $task['data_designacao']; ?></td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="desarquivar_id" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">Desarquivar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Não há tarefas arquivadas no momento.</p>
            <?php endif; ?>
        </section>

        <!-- Tarefas Encerradas -->
        <section id="encerradas" class="task-section">
            <h3 class="text-danger mt-5">Tarefas Encerradas</h3>
            <?php
            $encerradas = array_filter($tasks, function($task) {
                return $task['status'] === 'encerrada';
            });

            if (!empty($encerradas)): ?>
                <table class="table table-bordered table-striped task-table">
                    <thead class="table-danger">
                        <tr>
                            <th>ID da Tarefa</th>
                            <th>Usuário</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Data de Designação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($encerradas as $task): ?>
                            <tr>
                                <td><?php echo $task['id']; ?></td>
                                <td><?php echo htmlspecialchars($task['responsavel']); ?></td>
                                <td><?php echo htmlspecialchars($task['descricao']); ?></td>
                                <td><?php echo ucfirst($task['status']); ?></td>
                                <td><?php echo $task['data_designacao']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Não há tarefas encerradas no momento.</p>
            <?php endif; ?>
        </section>
    </div>

    <script>
        // Função para mostrar as tarefas de acordo com o botão clicado
        function showTasks(status) {
            // Esconde todas as seções
            const sections = document.querySelectorAll('.task-section');
            sections.forEach(section => section.style.display = 'none');

            // Mostra a seção correspondente
            const selectedSection = document.getElementById(status);
            if (selectedSection) {
                selectedSection.style.display = 'block';
            }
        }
    </script>
</body>
</html>
