<?php

// Verifica se a requisição foi feita via método POST (envio do formulário)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Processamento dos campos de texto (superglobal $_POST) ---
    // Recebe e sanitiza os dados para evitar injeção de código malicioso
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = htmlspecialchars(trim($_POST['email']));
    $tipo_projeto = htmlspecialchars(trim($_POST['tipo_projeto']));
    $metragem = htmlspecialchars(trim($_POST['metragem']));
    $mensagem = htmlspecialchars(trim($_POST['mensagem']));
    $tipo_contrato = htmlspecialchars(trim($_POST['tipo_contrato']));

    // Recebe os serviços adicionais, se existirem. O array 'servicos_adicionais' é opcional.
    $servicos_adicionais = isset($_POST['servicos_adicionais']) ? $_POST['servicos_adicionais'] : [];

    // --- Processamento do upload das fotos (superglobal $_FILES) ---
    // Define o diretório onde as fotos serão salvas.
    // Lembre-se de criar esta pasta no seu servidor e dar permissão de escrita.
    $diretorio_destino = "uploads/";
    
    // Cria o diretório se ele não existir
    if (!is_dir($diretorio_destino)) {
        mkdir($diretorio_destino, 0755, true);
    }
    
    // Array para armazenar os caminhos finais das fotos salvas
    $caminhos_fotos = [];
    
    // Define os tipos e tamanhos permitidos
    $tipos_permitidos = ['image/jpeg', 'image/png'];
    $tamanho_maximo = 2 * 1024 * 1024; // 2 MB em bytes

    // Verifica se os arquivos foram enviados corretamente
    if (isset($_FILES['fotos']) && is_array($_FILES['fotos']['name'])) {
        
        // Loop para processar cada foto enviada individualmente
        foreach ($_FILES['fotos']['name'] as $chave => $nome_arquivo) {
            
            // Verifica se não houve erro no upload do arquivo atual
            if ($_FILES['fotos']['error'][$chave] !== UPLOAD_ERR_OK) {
                continue; // Pula para o próximo arquivo se houver erro
            }
            
            $tipo_arquivo = $_FILES['fotos']['type'][$chave];
            $tamanho_arquivo = $_FILES['fotos']['size'][$chave];
            $nome_temporario = $_FILES['fotos']['tmp_name'][$chave];

            // 1. Validação do tipo do arquivo (MIME Type)
            if (!in_array($tipo_arquivo, $tipos_permitidos)) {
                echo "Erro: O arquivo '$nome_arquivo' não é um tipo de imagem válido.<br>";
                continue;
            }

            // 2. Validação do tamanho do arquivo
            if ($tamanho_arquivo > $tamanho_maximo) {
                echo "Erro: O arquivo '$nome_arquivo' excede o tamanho máximo de 2MB.<br>";
                continue;
            }

            // Cria um nome de arquivo único para evitar que fotos com o mesmo nome se sobrescrevam
            $extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
            $nome_final = uniqid('foto_') . '.' . $extensao;
            $caminho_final = $diretorio_destino . $nome_final;
            
            // Move o arquivo temporário do servidor para o diretório de destino
            if (move_uploaded_file($nome_temporario, $caminho_final)) {
                // Se o upload foi bem-sucedido, armazena o caminho da foto
                $caminhos_fotos[] = $caminho_final;
            }
        }
    }

    // --- Próximos passos (Lógica de Negócio) ---
    /*
     * A partir daqui, você pode usar as variáveis para:
     * 1. Enviar um e-mail de notificação para a sua equipe com os dados do orçamento e os caminhos das fotos.
     * 2. Calcular o valor do orçamento com base na metragem e nos serviços.
     * 3. Gerar o link de pagamento da Nubank API e redirecionar o cliente.
     */
     
    // Exemplo de como imprimir os dados para conferência (apenas para teste)
    echo "<h1>Dados Recebidos com Sucesso!</h1>";
    echo "Nome: $nome <br>";
    echo "Email: $email <br>";
    echo "Tipo de Projeto: $tipo_projeto <br>";
    echo "Metragem: $metragem m² <br>";
    echo "Mensagem: $mensagem <br>";
    echo "Tipo de Contrato: $tipo_contrato <br>";
    echo "Serviços Adicionais: " . implode(', ', $servicos_adicionais) . "<br>";
    echo "Caminhos das Fotos Salvas: " . implode(', ', $caminhos_fotos) . "<br>";

} else {
    // Redireciona o usuário para a página de orçamento se ele tentar acessar este arquivo diretamente
    header("Location: solicitar-orcamento.php");
    exit();
}

?>