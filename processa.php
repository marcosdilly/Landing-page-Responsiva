<?php
declare(strict_types=1);

/*
 * CONFIGURAÇÃO
 * Este arquivo precisa estar em uma hospedagem com PHP habilitado.
 * O e-mail de destino solicitado é marcoskim22@gmail.com.
 */

$destino = 'marcoskim22@gmail.com';
$consultor = 'Marcos';
$whatsappConsultor = '(31) 98283-0303';

// Só aceita POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Sanitização básica.
$nome = trim((string)($_POST['nome'] ?? ''));
$whatsapp = trim((string)($_POST['whatsapp'] ?? ''));
$aniversario = trim((string)($_POST['aniversario'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$veiculo = trim((string)($_POST['veiculo'] ?? ''));
$consentimento = trim((string)($_POST['consentimento'] ?? ''));

// Validação: NENHUM formulário incompleto é enviado.
$veiculosPermitidos = ['Carro', 'Moto', 'Caminhão'];
$whatsappNumeros = preg_replace('/\D+/', '', $whatsapp);

if (
    $nome === '' ||
    mb_strlen($nome) < 3 ||
    $whatsapp === '' ||
    strlen($whatsappNumeros) < 10 ||
    strlen($whatsappNumeros) > 11 ||
    $aniversario === '' ||
    $email === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !in_array($veiculo, $veiculosPermitidos, true) ||
    $consentimento !== 'Sim'
) {
    http_response_code(422);
    echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Dados incompletos</title>';
    echo '<body style="font-family:Arial;text-align:center;padding:60px">';
    echo '<h1>Confira os dados</h1><p>Preencha todos os campos obrigatórios corretamente e tente novamente.</p>';
    echo '<p><a href="index.html">Voltar para o formulário</a></p></body></html>';
    exit;
}

// Evita injeção de cabeçalho no e-mail.
$nomeSeguro = preg_replace("/[\r\n]+/", ' ', $nome);
$emailSeguro = preg_replace("/[\r\n]+/", '', $email);
$whatsappSeguro = preg_replace("/[\r\n]+/", ' ', $whatsapp);
$aniversarioSeguro = preg_replace("/[\r\n]+/", ' ', $aniversario);
$veiculoSeguro = preg_replace("/[\r\n]+/", ' ', $veiculo);

// Monta o e-mail.
$assunto = 'Novo Lead - APVS Brasil - ' . $veiculoSeguro;
$mensagem = "NOVO LEAD - APVS BRASIL\n\n";
$mensagem .= "Nome: {$nomeSeguro}\n";
$mensagem .= "WhatsApp: {$whatsappSeguro}\n";
$mensagem .= "Data de aniversário: {$aniversarioSeguro}\n";
$mensagem .= "E-mail: {$emailSeguro}\n";
$mensagem .= "Tipo de veículo: {$veiculoSeguro}\n";
$mensagem .= "Consentimento para contato: Sim\n";
$mensagem .= "Data/hora do envio: " . date('d/m/Y H:i:s') . "\n";

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'From: Site APVS <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'seudominio.com') . '>';
$headers[] = 'Reply-To: ' . $emailSeguro;

$enviado = mail($destino, $assunto, $mensagem, implode("\r\n", $headers));

if ($enviado) {
    header('Location: sucesso.html');
    exit;
}

// Se o servidor não estiver configurado para envio de e-mail.
http_response_code(500);
echo '<!doctype html><html lang="pt-BR"><meta charset="utf-8"><title>Não foi possível enviar</title>';
echo '<body style="font-family:Arial;text-align:center;padding:60px">';
echo '<h1>Não foi possível concluir o envio</h1>';
echo '<p>O formulário foi preenchido, mas o servidor de e-mail da hospedagem ainda precisa ser configurado.</p>';
echo '<p>Fale com o administrador da hospedagem para habilitar o envio de e-mails PHP.</p>';
echo '<p><a href="index.html">Voltar</a></p></body></html>';
?>
