<?php
    session_start();
    include 'log_atividade.php';

    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }

    // Verifica se o ID da tarefa foi passado via GET
    $task_id = isset($_GET['task_id']) ? $_GET['task_id'] : '';

    $tasksFile = 'json/tasks.json';  // Definindo a variável $tasksFile
    $logsFile = 'json/logs.json';

    // Verifica se o arquivo de tarefas existe e contém dados
    if (file_exists($tasksFile)) {
        $tasks = json_decode(file_get_contents($tasksFile), true);
    } else {
        $tasks = [];  // Se o arquivo não existir, cria um array vazio
    }

    // Verifica se o arquivo de logs existe e contém dados
    if (file_exists($logsFile)) {
        $logs = json_decode(file_get_contents($logsFile), true);
    } else {
        $logs = [];  // Se o arquivo não existir, cria um array vazio
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $task_id = $_POST['task_id'];
        $advogado_id = $_SESSION['usuario'];  // O ID do advogado será o nome de usuário
        $resposta = $_POST['resposta'];
        $arquivos = $_FILES['arquivos'];

        // Lógica para processar o arquivo anexo
        $caminho_arquivo_resposta = '';
        if ($arquivos['error'] === 0) {
            $diretorio = 'anexo/';
            $arquivo_nome = basename($arquivos['name']);
            $caminho_arquivo_resposta = $diretorio . $arquivo_nome;
        
            if (move_uploaded_file($arquivos['tmp_name'], $caminho_arquivo_resposta)) {
                echo "";
            } else {
                echo "";
            }
        } else {
            echo "";
        }
        
        }

        foreach ($tasks as &$task) {
            if ($task['id'] === $task_id) {
                // Adiciona a nova resposta como tarefa filha
                $task['respondida'][] = [
                    'id_pai' => $task['id'],
                    'resposta' => $resposta,
                    'solicitante' => $task['solicitante'],
                    'data_designacao' => $task['data_designacao'],
                    'data_resposta' => date('Y-m-d H:i:s'),
                    'tipo_tarefa' => $task['tipo_tarefa'],
                    'data_limite' => $task['data_limite'],
                    'anexo_resposta' => $caminho_arquivo_resposta
                ];
                // Atualiza o status da tarefa principal
                $task['status'] = 'respondida';
                break;
            }
        }

        // Atualiza os logs
        $log = [
            'task_id' => $task_id,
            'advogado_id' => $advogado_id,
            'resposta' => $resposta,
            'data_resposta' => date('Y-m-d H:i:s'),
        ];

        $logs[] = $log;

        // Salva as alterações no arquivo JSON
        file_put_contents($tasksFile, json_encode($tasks, JSON_PRETTY_PRINT));
        file_put_contents($logsFile, json_encode($logs, JSON_PRETTY_PRINT));

        // Mover o echo aqui para exibir a mensagem de sucesso acima do título
        $mensagem_sucesso = "<div class='alert alert-success mt-3'>Resposta registrada com sucesso!</div>";
    

    include "require/header.php";
    include "require/aside.php";

    // Buscar o nome do solicitante e o anexo
    $solicitante_nome = '';
    $anexo_url = '';
    if (!empty($task_id) && file_exists($tasksFile)) {
        foreach ($tasks as $task) {
            if ($task['id'] === $task_id) {
                $solicitante_nome = $task['solicitante'];  // Assumindo que o nome do solicitante está no campo 'solicitante'
                $anexo_url = isset($task['anexo']) ? $task['anexo'] : '';  // Verifica se existe um anexo
                break;
            }
        }
    }
?>
<link rel="stylesheet" href="/src/css/responder.css">
<div class="container py-1">
    <!-- Exibir a mensagem de sucesso acima do título -->
    <?php if (isset($mensagem_sucesso)) echo $mensagem_sucesso; ?>

    <h2>Responder Tarefa</h2>

    <!-- Exibir o anexo, se existir -->
    <?php if ($anexo_url): ?>
        <div class="mb-3">
            <h5>Anexo:</h5>
            <div class="btn-group">
                <a href="<?php echo $anexo_url; ?>" target="_blank" class="btn btn-custom-view" title="Visualizar Anexo">
                    <i class="bi bi-eye"></i> Visualizar
                </a>
                <a href="<?php echo $anexo_url; ?>" download class="btn btn-custom-download" title="Baixar Anexo">
                    <i class="bi bi-download"></i> Baixar
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulário para responder à tarefa -->
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="task_id" class="form-label">ID da Tarefa:</label>
            <input type="text" class="form-control" id="task_id" name="task_id" value="<?php echo htmlspecialchars($task_id); ?>" required readonly>
        </div>

        <div class="mb-3">
            <label for="solicitante" class="form-label">Nome do Solicitante:</label>
            <input type="text" class="form-control" id="solicitante" name="solicitante" value="<?php echo htmlspecialchars($solicitante_nome); ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="data_limite" class="form-label">Data Limite:</label>
            <input type="text" class="form-control" id="data_limite" name="data_limite" value="26/11/2024" readonly>
        </div>

        <div class="mb-3">
            <label for="instrucoes" class="form-label">Instruções:</label>
            <textarea class="form-control" id="instrucoes" name="instrucoes" rows="1" readonly>Teste</textarea>
        </div>

        <div class="mb-3">
            <label for="resposta" class="form-label">Resposta:</label>
            <textarea class="form-control" id="resposta" name="resposta" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label for="arquivos" class="form-label">Anexar Arquivos:</label>
            <input type="file" class="form-control" id="arquivos" name="arquivos">
        </div>

        <div class="btn-group">  <!-- Adicionando a classe btn-group aqui -->
            <button type="submit" class="btn btn-custom-response">Responder Tarefa</button>
            <a href="index.php" class="btn btn-custom-back">Voltar à Página Inicial</a>
        </div>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<?php include "require/footer.php"; ?>
</body>
</html>