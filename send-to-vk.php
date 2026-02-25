<?php
// ===== НАСТРОЙКИ =====
$vk_token = "ВАШ_ТОКЕН_СЮДА"; // Токен сообщества
$vk_group_id = 123456789; // ID сообщества (только цифры)

// ===== ПОЛУЧАЕМ ДАННЫЕ ИЗ ФОРМЫ =====
$name = $_POST['ФИО'] ?? 'Не указано';
$phone = $_POST['Телефон'] ?? 'Не указано';
$people = $_POST['Количество'] ?? '1';
$date = $_POST['Дата'] ?? 'Не указана';
$time = $_POST['Время'] ?? 'Не выбрано';
$comment = $_POST['Комментарий'] ?? 'Нет';

// Определяем стоимость
if ($people == 1) $price = '3 500₽';
elseif ($people == 2) $price = '5 000₽ (2 500₽/чел)';
else $price = '6 000₽ (2 000₽/чел)';

// ===== ФОРМИРУЕМ СООБЩЕНИЕ =====
$message = "🔔 **НОВАЯ ЗАЯВКА** 🔔\n\n";
$message .= "👤 **ФИО:** $name\n";
$message .= "📞 **Телефон:** $phone\n";
$message .= "👥 **Количество:** $people чел.\n";
$message .= "💰 **Стоимость:** $price\n";
$message .= "📅 **Дата:** $date\n";
$message .= "⏰ **Время:** $time\n";
$message .= "💬 **Комментарий:** $comment\n\n";
$message .= "🌀 Эмоциональный цикл | Иркутск";

// ===== ОТПРАВКА В ВК =====
// Сначала получим список диалогов, чтобы отправить самому себе
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.getConversations');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'access_token' => $vk_token,
    'v' => '5.131',
    'count' => 1
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);

// Если есть диалоги, отправляем в последний
if (isset($data['response']['items'][0]['conversation']['peer']['id'])) {
    $peer_id = $data['response']['items'][0]['conversation']['peer']['id'];
} else {
    // Если нет диалогов, отправляем в беседу с самим собой (группа)
    $peer_id = -$vk_group_id;
}

// Отправляем сообщение
curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/messages.send');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'access_token' => $vk_token,
    'v' => '5.131',
    'peer_id' => $peer_id,
    'message' => $message,
    'random_id' => rand(1, 999999999)
]));

$result = curl_exec($ch);
curl_close($ch);

// ===== ПЕРЕНАПРАВЛЯЕМ ОБРАТНО НА САЙТ =====
if ($result) {
    header("Location: index.html?status=success#booking");
} else {
    header("Location: index.html?status=error#booking");
}
exit;
?>
