<?php 

date_default_timezone_set('Asia/Tashkent'); // Toshkent vaqti

ob_start();

// ============================================
// BOT SOZLAMALARI
// ============================================
$API_KEY = '7543801387:AAEYijwLBuPusskqM7smy9BS8FQoOJDTeT0'; // Bot tokeni
$ADMIN_ID = 835400109; // Admin ID si

define('API_KEY', $API_KEY);

// ============================================
// ASOSIY FUNKSIYALAR
// ============================================

// API ga so'rov yuborish
function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    
    if (curl_error($ch)) {
        error_log(curl_error($ch));
        return false;
    }
    curl_close($ch);
    return json_decode($res);
}

// Xabar yuborish
function sendMessage($chat_id, $text, $keyboard = null, $parse_mode = 'HTML') {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $parse_mode
    ];
    
    if ($keyboard) {
        $data['reply_markup'] = json_encode($keyboard);
    }
    
    return bot('sendMessage', $data);
}

// Action yuborish (typing, upload_photo va h.k.)
function sendAction($chat_id, $action) {
    return bot('sendChatAction', [
        'chat_id' => $chat_id,
        'action' => $action
    ]);
}

// Xabarni tahrirlash
function editMessage($chat_id, $message_id, $text) {
    return bot('editMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text
    ]);
}

// Forward qilish
function forwardMessage($to_chat_id, $from_chat_id, $message_id) {
    return bot('forwardMessage', [
        'chat_id' => $to_chat_id,
        'from_chat_id' => $from_chat_id,
        'message_id' => $message_id
    ]);
}

// Rasm yuborish
function sendPhoto($chat_id, $photo, $caption = '') {
    return bot('sendPhoto', [
        'chat_id' => $chat_id,
        'photo' => $photo,
        'caption' => $caption
    ]);
}

// Object ni array ga o'tkazish
function objectToArray($object) {
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }
    return array_map('objectToArray', $object);
}

// ============================================
// MA'LUMOTLAR BAZASI FUNKSIYALARI
// ============================================

// User holatini olish
function getUserState($user_id) {
    $file = "data/$user_id/state.txt";
    if (file_exists($file)) {
        return file_get_contents($file);
    }
    return 'none';
}

// User holatini saqlash
function setUserState($user_id, $state) {
    if (!file_exists("data/$user_id")) {
        mkdir("data/$user_id", 0777, true);
    }
    file_put_contents("data/$user_id/state.txt", $state);
}

// User tokenini saqlash
function setUserToken($user_id, $token) {
    file_put_contents("data/$user_id/token.txt", $token);
}

// User tokenini olish
function getUserToken($user_id) {
    $file = "data/$user_id/token.txt";
    if (file_exists($file)) {
        return file_get_contents($file);
    }
    return '';
}

// User URL ini saqlash
function setUserUrl($user_id, $url) {
    file_put_contents("data/$user_id/url.txt", $url);
}

// User URL ini olish
function getUserUrl($user_id) {
    $file = "data/$user_id/url.txt";
    if (file_exists($file)) {
        return file_get_contents($file);
    }
    return '';
}

// Userni ro'yxatga olish
function registerUser($user_id) {
    if (!file_exists("data/$user_id")) {
        mkdir("data/$user_id", 0777, true);
    }
    
    $members = @file("data/members.txt", FILE_IGNORE_NEW_LINES);
    if (!$members || !in_array($user_id, $members)) {
        file_put_contents("data/members.txt", $user_id . PHP_EOL, FILE_APPEND);
    }
}

// Barcha userlarni olish
function getAllUsers() {
    $file = "data/members.txt";
    if (file_exists($file)) {
        return file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
    return [];
}

// ============================================
// TOKEN FUNKSIYALARI
// ============================================

// Tokenni tekshirish
function checkToken($token) {
    $url = "https://api.telegram.org/bot$token/getMe";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data;
}

// Webhook ma'lumotini olish
function getWebhookInfo($token) {
    $url = "https://api.telegram.org/bot$token/getWebhookInfo";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($response, true);
    return $data;
}

// Webhook sozlash
function setWebhook($token, $url) {
    $webhook_url = "https://api.telegram.org/bot$token/setWebhook?url=" . urlencode($url);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Webhook o'chirish
function deleteWebhook($token) {
    $url = "https://api.telegram.org/bot$token/deleteWebhook";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// ============================================
// KEYBOARD FUNKSIYALARI
// ============================================

// Asosiy menyu
function getMainKeyboard() {
    return [
        'keyboard' => [
            [['text' => '🔌 Token Ulash'], ['text' => '🗑 Token O\'chirish']],
            [['text' => '♻️ Ma\'lumot Olish'], ['text' => '👨‍💻 Admin Haqida']]
        ],
        'resize_keyboard' => true
    ];
}

// Orqaga qaytish menyusi
function getBackKeyboard() {
    return [
        'keyboard' => [
            [['text' => '🔙 Orqaga Qaytish']]
        ],
        'resize_keyboard' => true
    ];
}

// Admin panel menyusi
function getAdminKeyboard() {
    return [
        'keyboard' => [
            [['text' => '📊 Bot a\'zolari'], ['text' => '📢 Xabar yuborish']],
            [['text' => '🔄 Forward xabar'], ['text' => '📋 Statistika']],
            [['text' => '🏠 Asosiy menyu']]
        ],
        'resize_keyboard' => true
    ];
}

// ============================================
// ASOSIY LOGIKA
// ============================================

// Data papkasini yaratish
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}
if (!file_exists('data/members.txt')) {
    file_put_contents('data/members.txt', '');
}

// KELGAN MA'LUMOTLARNI OLISH
$update = json_decode(file_get_contents('php://input'));

// Xabarni tekshirish
if (!$update) {
    exit;
}

$message = $update->message ?? null;

// Agar xabar bo'lmasa, chiqish
if (!$message) {
    exit;
}

// Asosiy o'zgaruvchilar
$chat_id = $message->chat->id;
$user_id = $message->from->id;
$message_id = $message->message_id;
$text = $message->text ?? '';
$name = $message->from->first_name ?? 'Foydalanuvchi';

// Userni ro'yxatga olish
registerUser($user_id);

// User holatini olish
$state = getUserState($user_id);

// ============================================
// KOMANDALAR
// ============================================

// START KOMANDASI
if ($text == '/start') {
    sendAction($chat_id, 'typing');
    
    $welcome_text = "👋 Salom <a href='tg://user?id=$user_id'>$name</a>\n";
    $welcome_text .= "══════════════════════\n";
    $welcome_text .= "🤖 <b>Fs Webhook Bot</b> ga xush kelibsiz!\n";
    $welcome_text .= "══════════════════════\n";
    $welcome_text .= "✅ Bu bot orqali Telegram botlaringiz uchun:\n";
    $welcome_text .= "• Webhook sozlashingiz\n";
    $welcome_text .= "• Webhook o'chirishingiz\n";
    $welcome_text .= "• Bot ma'lumotlarini olishingiz mumkin\n";
    $welcome_text .= "══════════════════════\n";
    $welcome_text .= "📌 Kerakli menyudan foydalaning:";
    
    sendMessage($chat_id, $welcome_text, getMainKeyboard());
}

// ORQAGA QAYTISH
elseif ($text == '🔙 Orqaga Qaytish') {
    sendAction($chat_id, 'typing');
    setUserState($user_id, 'none');
    setUserToken($user_id, '');
    setUserUrl($user_id, '');
    
    sendMessage($chat_id, "🏠 Bosh menyuga qaytdingiz", getMainKeyboard());
}

// TOKEN ULASH
elseif ($text == '🔌 Token Ulash') {
    sendAction($chat_id, 'typing');
    setUserState($user_id, 'waiting_token');
    setUserToken($user_id, '');
    setUserUrl($user_id, '');
    
    $msg = "📎 <b>Token ulash</b>\n";
    $msg .= "══════════════════════\n";
    $msg .= "Botingizning tokenini yuboring:\n";
    $msg .= "Masalan: <code>1234567890:ABCdefGHIjklMNOpqrsTUVwxyz</code>";
    
    sendMessage($chat_id, $msg, getBackKeyboard());
}

// TOKEN O'CHIRISH
elseif ($text == '🗑 Token O\'chirish') {
    sendAction($chat_id, 'typing');
    setUserState($user_id, 'waiting_delete');
    setUserToken($user_id, '');
    
    $msg = "🗑 <b>Token o'chirish</b>\n";
    $msg .= "══════════════════════\n";
    $msg .= "O'chirmoqchi bo'lgan botingiz tokenini yuboring:";
    
    sendMessage($chat_id, $msg, getBackKeyboard());
}

// MA'LUMOT OLISH
elseif ($text == '♻️ Ma\'lumot Olish') {
    sendAction($chat_id, 'typing');
    setUserState($user_id, 'waiting_info');
    setUserToken($user_id, '');
    
    $msg = "📊 <b>Ma'lumot olish</b>\n";
    $msg .= "══════════════════════\n";
    $msg .= "Ma'lumot olish uchun bot tokenini yuboring:";
    
    sendMessage($chat_id, $msg, getBackKeyboard());
}

// ADMIN HAQIDA
elseif ($text == '👨‍💻 Admin Haqida') {
    sendAction($chat_id, 'typing');
    
    $admin_info = "👨‍💻 <b>Admin haqida ma'lumot</b>\n";
    $admin_info .= "══════════════════════\n";
    $admin_info .= "🆔 ID: <code>$ADMIN_ID</code>\n";
    $admin_info .= "📱 Telegram: @Gold_Bloger\n";
    $admin_info .= "💬 Murojaat: @Gold_Bloger\n";
    $admin_info .= "══════════════════════\n";
    $admin_info .= "⚙️ Bot versiyasi: 2.0.0\n";
    $admin_info .= "📅 Yangilangan: 2026";
    
    sendMessage($chat_id, $admin_info, getMainKeyboard());
}

// TOKEN KUTILAYOTGAN HOLAT
elseif ($state == 'waiting_token') {
    sendAction($chat_id, 'typing');
    $token = $text;
    
    // Tokenni tekshirish
    $check = checkToken($token);
    
    if ($check && $check['ok']) {
        $bot_info = $check['result'];
        setUserToken($user_id, $token);
        setUserState($user_id, 'waiting_url');
        
        $msg = "✅ <b>Token tasdiqlandi!</b>\n";
        $msg .= "══════════════════════\n";
        $msg .= "🤖 Nomi: <b>{$bot_info['first_name']}</b>\n";
        $msg .= "📝 Username: @{$bot_info['username']}\n";
        $msg .= "🆔 ID: <code>{$bot_info['id']}</code>\n";
        $msg .= "══════════════════════\n";
        $msg .= "📎 Endi PHP faylingiz joylashgan URL manzilini yuboring:\n";
        $msg .= "Masalan: <code>https://sizninghostingiz.com/bot.php</code>";
        
        sendMessage($chat_id, $msg, getBackKeyboard());
    } else {
        sendMessage($chat_id, "❌ Noto'g'ri token! Iltimos, qaytadan urinib ko'ring:", getBackKeyboard());
    }
}

// URL KUTILAYOTGAN HOLAT
elseif ($state == 'waiting_url') {
    sendAction($chat_id, 'typing');
    $url = $text;
    $token = getUserToken($user_id);
    
    // URL ni tekshirish
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        sendMessage($chat_id, "❌ Noto'g'ri URL manzil! Qaytadan yuboring:", getBackKeyboard());
    } else {
        setUserUrl($user_id, $url);
        setUserState($user_id, 'confirm_webhook');
        
        $msg = "🔍 <b>Kiritgan ma'lumotlaringizni tekshiring:</b>\n";
        $msg .= "══════════════════════\n";
        $msg .= "🤖 Token: <code>$token</code>\n";
        $msg .= "📍 URL: <code>$url</code>\n";
        $msg .= "══════════════════════\n";
        $msg .= "✅ Hammasi to'g'ri bo'lsa, pastdagi tugmani bosing:";
        
        $keyboard = [
            'keyboard' => [
                [['text' => '✅ Webhook sozlash']],
                [['text' => '🔙 Orqaga Qaytish']]
            ],
            'resize_keyboard' => true
        ];
        
        sendMessage($chat_id, $msg, $keyboard);
    }
}

// WEBHOOK SOZLASH TUGMASI
elseif ($text == '✅ Webhook sozlash') {
    sendAction($chat_id, 'typing');
    $token = getUserToken($user_id);
    $url = getUserUrl($user_id);
    
    if (!$token || !$url) {
        sendMessage($chat_id, "❌ Ma'lumotlar topilmadi. Qaytadan urinib ko'ring.", getMainKeyboard());
        setUserState($user_id, 'none');
    } else {
        $status_msg = sendMessage($chat_id, "🔄 Webhook sozlanmoqda, iltimos kuting...");
        $status_msg_id = $status_msg->result->message_id ?? null;
        
        // Webhook sozlash
        $result = setWebhook($token, $url);
        
        if ($status_msg_id) {
            editMessage($chat_id, $status_msg_id, "✅ Webhook sozlandi!");
        }
        
        if ($result && $result['ok']) {
            $msg = "✅ <b>Webhook muvaffaqiyatli sozlandi!</b>\n";
            $msg .= "══════════════════════\n";
            $msg .= "📍 URL: <code>$url</code>\n";
            $msg .= "══════════════════════\n";
            $msg .= "Endi botingiz ishga tayyor!";
        } else {
            $msg = "❌ <b>Xatolik yuz berdi!</b>\n";
            $msg .= "══════════════════════\n";
            $msg .= "Sabab: " . ($result['description'] ?? 'Noma\'lum xato');
        }
        
        setUserState($user_id, 'none');
        setUserToken($user_id, '');
        setUserUrl($user_id, '');
        
        sendMessage($chat_id, $msg, getMainKeyboard());
    }
}

// MA'LUMOT OLISH UCHUN TOKEN KUTISH
elseif ($state == 'waiting_info') {
    sendAction($chat_id, 'typing');
    $token = $text;
    
    $check = checkToken($token);
    
    if ($check && $check['ok']) {
        $bot_info = $check['result'];
        
        // Webhook ma'lumotini olish
        $webhook = getWebhookInfo($token);
        $webhook_url = ($webhook && $webhook['ok']) ? $webhook['result']['url'] : '❌ O\'rnatilmagan';
        
        $msg = "📊 <b>Bot ma'lumotlari</b>\n";
        $msg .= "══════════════════════\n";
        $msg .= "🤖 Nomi: <b>{$bot_info['first_name']}</b>\n";
        $msg .= "📝 Username: @{$bot_info['username']}\n";
        $msg .= "🆔 ID: <code>{$bot_info['id']}</code>\n";
        $msg .= "══════════════════════\n";
        $msg .= "📍 Webhook URL:\n<code>$webhook_url</code>\n";
        $msg .= "══════════════════════\n";
        $msg .= "✅ Ma'lumotlar olindi!";
        
        setUserState($user_id, 'none');
        sendMessage($chat_id, $msg, getMainKeyboard());
    } else {
        sendMessage($chat_id, "❌ Noto'g'ri token! Qaytadan urinib ko'ring:", getBackKeyboard());
    }
}

// TOKEN O'CHIRISH UCHUN KUTISH
elseif ($state == 'waiting_delete') {
    sendAction($chat_id, 'typing');
    $token = $text;
    
    $check = checkToken($token);
    
    if ($check && $check['ok']) {
        $bot_info = $check['result'];
        
        $confirm_msg = "🗑 <b>Webhook o'chirish</b>\n";
        $confirm_msg .= "══════════════════════\n";
        $confirm_msg .= "🤖 Bot: <b>{$bot_info['first_name']}</b>\n";
        $confirm_msg .= "📝 @{$bot_info['username']}\n";
        $confirm_msg .= "══════════════════════\n";
        $confirm_msg .= "Haqiqatan ham webhook ni o'chirmoqchimisiz?";
        
        $keyboard = [
            'keyboard' => [
                [['text' => '✅ Ha, o\'chirish']],
                [['text' => '🔙 Orqaga Qaytish']]
            ],
            'resize_keyboard' => true
        ];
        
        setUserToken($user_id, $token);
        setUserState($user_id, 'confirm_delete');
        
        sendMessage($chat_id, $confirm_msg, $keyboard);
    } else {
        sendMessage($chat_id, "❌ Noto'g'ri token! Qaytadan urinib ko'ring:", getBackKeyboard());
    }
}

// O'CHIRISHNI TASDIQLASH
elseif ($text == '✅ Ha, o\'chirish' && $state == 'confirm_delete') {
    sendAction($chat_id, 'typing');
    $token = getUserToken($user_id);
    
    if (!$token) {
        sendMessage($chat_id, "❌ Token topilmadi!", getMainKeyboard());
        setUserState($user_id, 'none');
    } else {
        $status_msg = sendMessage($chat_id, "🔄 Webhook o'chirilmoqda...");
        $status_msg_id = $status_msg->result->message_id ?? null;
        
        // Webhook o'chirish
        $result = deleteWebhook($token);
        
        if ($status_msg_id) {
            editMessage($chat_id, $status_msg_id, "✅ Webhook o'chirildi!");
        }
        
        if ($result && $result['ok']) {
            $msg = "🗑 <b>Webhook muvaffaqiyatli o'chirildi!</b>\n";
            $msg .= "══════════════════════\n";
            $msg .= "Endi botingiz webhook dan uzildi.";
        } else {
            $msg = "❌ <b>Xatolik yuz berdi!</b>\n";
            $msg .= "══════════════════════\n";
            $msg .= "Sabab: " . ($result['description'] ?? 'Noma\'lum xato');
        }
        
        setUserState($user_id, 'none');
        setUserToken($user_id, '');
        
        sendMessage($chat_id, $msg, getMainKeyboard());
    }
}

// ============================================
// ADMIN PANEL
// ============================================

elseif ($text == '/panel' && $chat_id == $ADMIN_ID) {
    sendAction($chat_id, 'typing');
    
    $users = getAllUsers();
    $user_count = count($users);
    
    $panel_text = "👑 <b>Admin Panel</b>\n";
    $panel_text .= "══════════════════════\n";
    $panel_text .= "📊 Bot statistikasi:\n";
    $panel_text .= "👥 Foydalanuvchilar: <b>$user_count</b>\n";
    $panel_text .= "══════════════════════\n";
    $panel_text .= "Kerakli bo'limni tanlang:";
    
    sendMessage($chat_id, $panel_text, getAdminKeyboard());
}

// BOT A'ZOLARI SONI
elseif ($text == '📊 Bot a\'zolari' && $chat_id == $ADMIN_ID) {
    sendAction($chat_id, 'typing');
    
    $users = getAllUsers();
    $count = count($users);
    
    $msg = "📊 <b>Bot a'zolari</b>\n";
    $msg .= "══════════════════════\n";
    $msg .= "👥 Jami foydalanuvchilar: <b>$count</b> ta\n";
    $msg .= "══════════════════════\n";
    
    if ($count > 0) {
        $msg .= "📋 Oxirgi 10 ta user ID:\n";
        $last_users = array_slice($users, -10);
        foreach ($last_users as $user) {
            $msg .= "<code>$user</code>\n";
        }
    }
    
    sendMessage($chat_id, $msg, getAdminKeyboard());
}

// XABAR YUBORISH
elseif ($text == '📢 Xabar yuborish' && $chat_id == $ADMIN_ID) {
    sendAction($chat_id, 'typing');
    setUserState($user_id, 'admin_broadcast');
    
    $msg = "📢 <b>Xabar yuborish</b>\n";
    $msg .= "══════════════════════\n";
    $msg .= "Barcha foydalanuvchilarga yubormoqchi bo'lgan xabaringizni yozing:";
    
    sendMessage($chat_id, $msg, getBackKeyboard());
}

// XABAR YUBORISH JARAYONI
elseif ($state == 'admin_broadcast' && $chat_id == $ADMIN_ID) {
    sendAction($chat_id, 'typing');
    
    $users = getAllUsers();
    $success = 0;
    $failed = 0;
    
    $status_msg = sendMessage($chat_id, "🔄 Xabar yuborilmoqda...\nJami: " . count($users) . " ta foydalanuvchi");
    
    foreach ($users as $user) {
        if (!empty(trim($user))) {
            $result = sendMessage(trim($user), $text);
            if ($result && isset($result->ok) && $result->ok) {
                $success++;
            } else {
                $failed++;
            }
            usleep(50000); // 0.05 soniya kutish
        }
    }
    
    $report = "📊 <b>Xabar yuborish yakunlandi!</b>\n";
    $report .= "══════════════════════\n";
    $report .= "✅ Yuborildi: <b>$success</b>\n";
    $report .= "❌ Yuborilmadi: <b>$failed</b>\n";
    $report .= "══════════════════════\n";
    $report .= "Jami: <b>" . ($success + $failed) . "</b>";
    
    setUserState($user_id, 'none');
    sendMessage($chat_id, $report, getAdminKeyboard());
}

// FORWARD XABAR
elseif ($text == '🔄 Forward xabar' && $chat_id == $ADMIN_ID) {
    sendAction($chat_id, 'typing');
    setUserState($user_id, 'admin_forward');
    
    $msg = "🔄 <b>Forward xabar</b>\n";
    $msg .= "══════════════════════\n";
    $msg .= "Forward qilmoqchi bo'lgan xabarni menga yuboring:";
    
    sendMessage($chat_id, $msg, getBackKeyboard());
}

// FORWARD XABAR JARAYONI
elseif ($state == 'admin_forward' && $chat_id == $ADMIN_ID) {
    sendAction($chat_id, 'typing');
    
    if (isset($message->forward_from) || isset($message->forward_from_chat)) {
        $users = getAllUsers();
        $forward_msg_id = $message_id;
        $success = 0;
        $failed = 0;
        
        $status_msg = sendMessage($chat_id, "🔄 Xabar forward qilinmoqda...\nJami: " . count($users) . " ta foydalanuvchi");
        
        foreach ($users as $user) {
            if (!empty(trim($user))) {
                $result = forwardMessage(trim($user), $chat_id, $forward_msg_id);
                if ($result && isset($result->ok) && $result->ok) {
                    $success++;
                } else {
                    $failed++;
                }
                usleep(50000);
            }
        }
        
        $report = "📊 <b>Forward yakunlandi!</b>\n";
        $report .= "══════════════════════\n";
        $report .= "✅ Yuborildi: <b>$success</b>\n";
        $report .= "❌ Yuborilmadi: <b>$failed</b>\n";
        $report .= "══════════════════════\n";
        $report .= "Jami: <b>" . ($success + $failed) . "</b>";
        
        setUserState($user_id, 'none');
        sendMessage($chat_id, $report, getAdminKeyboard());
    } else {
        sendMessage($chat_id, "❌ Iltimos, forward qilinadigan xabarni yuboring!", getBackKeyboard());
    }
}

// STATISTIKA
elseif ($text == '📋 Statistika' && $chat_id == $ADMIN_ID) {
    sendAction($chat_id, 'typing');
    
    $users = getAllUsers();
    $user_count = count($users);
    
    // Data papkasidagi fayllar soni
    $data_folders = glob('data/*', GLOB_ONLYDIR);
    $folder_count = count($data_folders) - 1; // members.txt ni hisobga olmaslik
    
    $stats = "📋 <b>Bot statistikasi</b>\n";
    $stats .= "══════════════════════\n";
    $stats .= "👥 Foydalanuvchilar: <b>$user_count</b>\n";
    $stats .= "📁 Data papkalar: <b>$folder_count</b>\n";
    $stats .= "══════════════════════\n";
    $stats .= "🕐 So'nggi aktivlik: " . date('Y-m-d H:i:s');
    
    sendMessage($chat_id, $stats, getAdminKeyboard());
}

// ASOSIY MENYU
elseif ($text == '🏠 Asosiy menyu') {
    sendAction($chat_id, 'typing');
    setUserState($user_id, 'none');
    setUserToken($user_id, '');
    setUserUrl($user_id, '');
    
    sendMessage($chat_id, "🏠 Asosiy menyu", getMainKeyboard());
}

// NOMA'LUM XABAR
else {
    sendAction($chat_id, 'typing');
    
    $unknown_msg = "❌ Noto'g'ri buyruq!\n";
    $unknown_msg .= "══════════════════════\n";
    $unknown_msg .= "Iltimos, quyidagi menyulardan birini tanlang:";
    
    sendMessage($chat_id, $unknown_msg, getMainKeyboard());
}

ob_end_flush();
?>
