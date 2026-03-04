<?php
/**
 * Database Initialization Script
 * Populates the database with comprehensive sample data
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Initialization - Runyakitara Hub</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            color: #333;
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #17a2b8;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        ul {
            line-height: 1.8;
        }
    </style>
</head>
<body>
<div class='container'>";

try {
    $db = getDBConnection();
    
    echo "<h1>🚀 Initializing Runyakitara Hub Database</h1>";
    
    // Clear existing data (except admin user)
    echo "<div class='info'>📋 Clearing existing data...</div>";
    $db->exec("DELETE FROM lessons");
    $db->exec("DELETE FROM dictionary");
    $db->exec("DELETE FROM proverbs");
    $db->exec("DELETE FROM articles");
    $db->exec("DELETE FROM translations");
    $db->exec("DELETE FROM grammar_topics");
    $db->exec("DELETE FROM media");
    
    // Insert comprehensive lessons
    echo "<div class='info'>📚 Adding lessons...</div>";
    $lessons = [
        ['Introduction to Runyakitara', 'Learn about the language family and its speakers', 'Runyakitara is a collective term for closely related Bantu languages spoken in southwestern Uganda and parts of Rwanda. The term encompasses Runyoro, Rutooro, Runyankore, and Rukiga. These languages share significant mutual intelligibility and cultural heritage.', 'beginner', 1],
        ['Alphabet & Pronunciation', 'Master the sounds and letters of Runyakitara', 'The Runyakitara alphabet uses Latin script with 24 letters. Key pronunciation rules: "r" is rolled, "ny" sounds like Spanish "ñ", vowels are pure (a=ah, e=eh, i=ee, o=oh, u=oo). Practice these sounds to build a strong foundation.', 'beginner', 2],
        ['Basic Greetings', 'Essential phrases for daily interaction', 'Oraire ota? (How are you?) - Ndi mpora (I am fine). Osiibire ota? (How did you sleep?) - Nsiibire mpora (I slept well). Webale (Thank you). Kare (Goodbye). These greetings are fundamental to Banyakitara culture.', 'beginner', 3],
        ['Numbers and Counting', 'Learn to count from 1 to 100', 'Emwe (1), Ibiri (2), Ishatu (3), Ina (4), Itaano (5), Mukaga (6), Mushanju (7), Munaana (8), Mwenda (9), Ikumi (10). Understanding numbers is essential for daily transactions and time-telling.', 'beginner', 4],
        ['Family Members', 'Vocabulary for family relationships', 'Taata (Father), Maama (Mother), Mwana (Child), Mukazi (Wife), Omushaija (Husband), Nyinaze (Sister), Mwanyinaze (Brother). Family is central to Banyakitara culture.', 'beginner', 5],
        ['Common Verbs', 'Essential action words', 'Kugenda (to go), Kuija (to come), Kurya (to eat), Kunywa (to drink), Kushoma (to read), Kwandika (to write), Kwogera (to speak). Master these verbs for basic communication.', 'intermediate', 6],
        ['Noun Classes', 'Understanding Bantu noun classification', 'Runyakitara uses noun classes with prefixes: Omu- (singular person), Aba- (plural people), Eki- (singular thing), Ebi- (plural things). This system affects agreement with adjectives and verbs.', 'intermediate', 7],
        ['Verb Conjugation', 'How to conjugate verbs in different tenses', 'Present: N-kora (I do), Past: N-ka-kora (I did), Future: N-ri-kora (I will do). Understanding tense markers is crucial for expressing time accurately.', 'advanced', 8]
    ];
    
    $stmt = $db->prepare("INSERT INTO lessons (title, description, content, level, lesson_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($lessons as $lesson) {
        $stmt->execute($lesson);
    }
    echo "<div class='success'>✅ Added " . count($lessons) . " lessons</div>";
    
    // Insert comprehensive dictionary entries
    echo "<div class='info'>📖 Adding dictionary entries...</div>";
    $words = [
        ['Omuntu', 'Person', 'People', 'oh-moon-too', 'Omuntu oyo ni murungi (That person is good)'],
        ['Ente', 'Cow', 'Animals', 'en-teh', 'Ente yaitu ni nrungi (Our cow is good)'],
        ['Omugyenyi', 'Guest', 'People', 'oh-moo-gyen-yee', 'Omugyenyi aija leero (The guest comes today)'],
        ['Oraire', 'Hello/Greetings', 'Greetings', 'oh-rye-reh', 'Oraire ota? (How are you?)'],
        ['Ensi', 'Land/Country', 'Places', 'en-see', 'Ensi yaitu ni nrungi (Our country is beautiful)'],
        ['Amaizi', 'Water', 'Nature', 'ah-my-zee', 'Amaizi ni marungi (The water is clean)'],
        ['Omwana', 'Child', 'People', 'oh-mwa-na', 'Omwana arikushoma (The child is reading)'],
        ['Enyumba', 'House', 'Buildings', 'en-yoom-ba', 'Enyumba yaitu ni nkuru (Our house is big)'],
        ['Ekyakulya', 'Food', 'Food & Drink', 'eh-cha-koo-lya', 'Ekyakulya kiri mpora (The food is good)'],
        ['Omugati', 'Bread', 'Food & Drink', 'oh-moo-ga-tee', 'Omugati ni murungi (The bread is good)'],
        ['Ekitabo', 'Book', 'Education', 'eh-kee-ta-bo', 'Ekitabo kiri mpora (The book is good)'],
        ['Omukozi', 'Worker', 'Occupations', 'oh-moo-ko-zee', 'Omukozi arikukora (The worker is working)'],
        ['Omushaija', 'Man/Husband', 'People', 'oh-moo-shy-ja', 'Omushaija arikugenda (The man is going)'],
        ['Omukazi', 'Woman/Wife', 'People', 'oh-moo-ka-zee', 'Omukazi arikuija (The woman is coming)'],
        ['Eizoba', 'Sun', 'Nature', 'eh-ee-zo-ba', 'Eizoba ririkwaka (The sun is shining)'],
        ['Omwezi', 'Moon', 'Nature', 'oh-mwe-zee', 'Omwezi uri omu iguru (The moon is in the sky)'],
        ['Emiti', 'Trees', 'Nature', 'eh-mee-tee', 'Emiti ni mirungi (The trees are beautiful)'],
        ['Ebisatu', 'Shoes', 'Clothing', 'eh-bee-sa-too', 'Ebisatu byangye ni bishya (My shoes are new)'],
        ['Ekyambaro', 'Clothing', 'Clothing', 'eh-cham-ba-ro', 'Ekyambaro kiri kirungi (The clothing is nice)'],
        ['Omugongo', 'Back', 'Body Parts', 'oh-moo-gon-go', 'Omugongo gwangye gurikubabara (My back is hurting)']
    ];
    
    $stmt = $db->prepare("INSERT INTO dictionary (word_runyakitara, word_english, category, pronunciation, example_sentence) VALUES (?, ?, ?, ?, ?)");
    foreach ($words as $word) {
        $stmt->execute($word);
    }
    echo "<div class='success'>✅ Added " . count($words) . " dictionary entries</div>";
    
    // Insert proverbs
    echo "<div class='info'>💭 Adding proverbs...</div>";
    $proverbs = [
        ['Omugurusi tarikuza omu mwanya', 'An old person does not grow in one place', 'Experience comes from traveling and learning from different places. This proverb encourages exploration and learning from diverse experiences.'],
        ['Akahurira kazoora', 'What is delayed will eventually come', 'Patience is rewarded; good things come to those who wait. This teaches the value of perseverance and patience.'],
        ['Omwana taba omugurusi', 'A child is not an elder', 'Youth should respect and learn from elders. This emphasizes the importance of respecting wisdom and experience.'],
        ['Enkoko ekwata eky\'omu', 'A hen scratches in one place', 'Consistency and focus lead to success. This proverb teaches the value of dedication and persistence.'],
        ['Akaana kazooba karikuza', 'A child who will grow is growing', 'Progress happens gradually. This reminds us that development takes time and patience.'],
        ['Omuti ogutakozesibwa tigukura', 'A tree that is not used does not grow', 'Practice makes perfect. Skills develop through use and application.']
    ];
    
    $stmt = $db->prepare("INSERT INTO proverbs (proverb_text, translation, meaning) VALUES (?, ?, ?)");
    foreach ($proverbs as $proverb) {
        $stmt->execute($proverb);
    }
    echo "<div class='success'>✅ Added " . count($proverbs) . " proverbs</div>";
    
    // Insert articles
    echo "<div class='info'>📰 Adding news articles...</div>";
    $articles = [
        ['The Importance of Preserving Runyakitara Languages', 'Language preservation is crucial for maintaining cultural identity and heritage. Runyakitara languages carry centuries of wisdom, traditions, and unique worldviews. As globalization increases, it becomes even more important to document and teach these languages to younger generations. Digital platforms like this hub play a vital role in making language learning accessible and engaging.', 'Exploring why language preservation matters for cultural identity...', 'Admin', date('Y-m-d')],
        ['Famous Banyakitara Personalities', 'The Banyakitara people have produced many influential figures in politics, arts, and academia. From traditional kingdom leaders to modern-day innovators, these personalities have shaped the region\'s history and continue to inspire future generations. Their stories demonstrate the rich cultural heritage and potential of the Banyakitara community.', 'Celebrating influential figures from Banyakitara culture...', 'Admin', date('Y-m-d', strtotime('-5 days'))],
        ['Traditional Music and Dance', 'Music and dance are integral to Banyakitara culture. Traditional instruments like the engoma (drum) and endingidi (tube fiddle) create unique sounds that accompany ceremonies and celebrations. Understanding these art forms provides insight into the community\'s values and history.', 'Exploring the rich musical heritage of the Banyakitara...', 'Admin', date('Y-m-d', strtotime('-10 days'))],
        ['Modern Technology Meets Ancient Language', 'How digital tools are revolutionizing language learning and preservation. Mobile apps, online dictionaries, and interactive platforms are making it easier than ever to learn Runyakitara. This technological integration ensures the language remains relevant for digital-native generations.', 'The intersection of technology and language preservation...', 'Admin', date('Y-m-d', strtotime('-15 days'))]
    ];
    
    $stmt = $db->prepare("INSERT INTO articles (title, content, excerpt, author, published_date) VALUES (?, ?, ?, ?, ?)");
    foreach ($articles as $article) {
        $stmt->execute($article);
    }
    echo "<div class='success'>✅ Added " . count($articles) . " articles</div>";
    
    // Insert translations
    echo "<div class='info'>🌍 Adding translations...</div>";
    $translations = [
        ['The Ant and the Grasshopper', 'story', 'A traditional fable about preparation and hard work...', 'Engigye n\'ekisusunku: Olugero lw\'okwetegeka n\'okukora ennyo...', 'This story teaches children the importance of planning ahead and working diligently. It\'s commonly told during harvest season.'],
        ['Wedding Song', 'song', 'Congratulations on your union, may your love grow strong...', 'Mwebale kuhikirana, omukwano gwenyu gukure...', 'Traditional wedding songs celebrate the union of families and wish prosperity to the new couple.'],
        ['Harvest Poem', 'poem', 'The fields are golden, our work is done...', 'Eby\'omu murima birabika, emirimu yaitu erikoma...', 'Harvest poems express gratitude for successful crops and celebrate community cooperation.']
    ];
    
    $stmt = $db->prepare("INSERT INTO translations (title, type, original_text, translated_text, cultural_context) VALUES (?, ?, ?, ?, ?)");
    foreach ($translations as $translation) {
        $stmt->execute($translation);
    }
    echo "<div class='success'>✅ Added " . count($translations) . " translations</div>";
    
    // Insert grammar topics
    echo "<div class='info'>📝 Adding grammar topics...</div>";
    $grammar = [
        ['Noun Classes and Prefixes', 'Runyakitara uses a system of noun classes inherited from Proto-Bantu. Each class has specific prefixes for singular and plural forms. Understanding these classes is fundamental to proper grammar.', 'Class 1/2: Omu-/Aba- (people), Class 3/4: Omu-/Emi- (plants), Class 5/6: Eri-/Ama- (paired items), Class 7/8: Eki-/Ebi- (things)'],
        ['Verb Tenses', 'Runyakitara has several tenses marked by infixes. The present tense uses -ri-, past uses -ka-, and future uses -ri- with context.', 'Present: Ndirikukora (I am working), Past: Ndakakora (I worked), Future: Ndirikukora (I will work)'],
        ['Possessive Pronouns', 'Possessive forms agree with the noun class of the possessed item, not the possessor.', 'My book: Ekitabo kyangye, Your house: Enyumba yawe, Our land: Ensi yaitu'],
        ['Question Formation', 'Questions are formed using question words and intonation. Common question words include ki (what), ha (where), ndi (who).', 'What is this?: Niki ekyo?, Where are you going?: Urikugenda ha?, Who is that?: Ndi oyo?']
    ];
    
    $stmt = $db->prepare("INSERT INTO grammar_topics (title, content, examples) VALUES (?, ?, ?)");
    foreach ($grammar as $topic) {
        $stmt->execute($topic);
    }
    echo "<div class='success'>✅ Added " . count($grammar) . " grammar topics</div>";
    
    // Insert media entries
    echo "<div class='info'>🎵 Adding media resources...</div>";
    $media = [
        ['Basic Pronunciation Guide', 'Audio guide for learning correct pronunciation', 'audio', 'Pronunciation', '/media/audio/pronunciation-basics.mp3'],
        ['Greetings Practice', 'Practice common greetings with native speakers', 'audio', 'Greetings', '/media/audio/greetings.mp3'],
        ['Traditional Song: Omukwano', 'A traditional friendship song', 'audio', 'Music', '/media/audio/omukwano-song.mp3'],
        ['Lesson 1: Introduction', 'Video introduction to Runyakitara', 'video', 'Lessons', '/media/video/lesson-1-intro.mp4'],
        ['Cultural Dance Performance', 'Traditional Banyakitara dance', 'video', 'Culture', '/media/video/traditional-dance.mp4']
    ];
    
    $stmt = $db->prepare("INSERT INTO media (title, description, type, category, file_path) VALUES (?, ?, ?, ?, ?)");
    foreach ($media as $item) {
        $stmt->execute($item);
    }
    echo "<div class='success'>✅ Added " . count($media) . " media resources</div>";
    
    // Display summary
    echo "<h2>✨ Database Initialization Complete!</h2>";
    echo "<div class='success'>";
    echo "<h3>Summary:</h3>";
    echo "<ul>";
    echo "<li>✅ " . count($lessons) . " Lessons</li>";
    echo "<li>✅ " . count($words) . " Dictionary Entries</li>";
    echo "<li>✅ " . count($proverbs) . " Proverbs</li>";
    echo "<li>✅ " . count($articles) . " News Articles</li>";
    echo "<li>✅ " . count($translations) . " Translations</li>";
    echo "<li>✅ " . count($grammar) . " Grammar Topics</li>";
    echo "<li>✅ " . count($media) . " Media Resources</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='info'>";
    echo "<h3>🔐 Admin Login Credentials:</h3>";
    echo "<p><strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin-top: 30px;'>";
    echo "<a href='index.html' class='btn'>🏠 Go to Homepage</a>";
    echo "<a href='admin/login.php' class='btn'>🔐 Admin Login</a>";
    echo "</div>";
    
    closeDBConnection($db);
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}

echo "</div></body></html>";
?>
