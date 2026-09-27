<?php

declare(strict_types=1);

$hash = '$2y$10$e3FL9XkXcgOSvARuke8DUOWH22Zco95S4wqpXEfyW/x4o.loUEAtK';

function q(?string $value): string
{
    if ($value === null) {
        return 'NULL';
    }
    return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
}

function article(array $blocks): string
{
    $html = '';
    foreach ($blocks as $block) {
        $type = $block[0];
        if ($type === 'p') {
            $html .= '<p>' . htmlspecialchars($block[1], ENT_QUOTES, 'UTF-8') . '</p>';
        } elseif ($type === 'h2') {
            $html .= '<h2>' . htmlspecialchars($block[1], ENT_QUOTES, 'UTF-8') . '</h2>';
        } elseif ($type === 'quote') {
            $html .= '<blockquote><p>' . htmlspecialchars($block[1], ENT_QUOTES, 'UTF-8') . '</p></blockquote>';
        } elseif ($type === 'ul') {
            $html .= '<ul>';
            foreach ($block[1] as $item) {
                $html .= '<li>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul>';
        } elseif ($type === 'code') {
            $html .= '<pre><code class="language-php">' . htmlspecialchars($block[1], ENT_QUOTES, 'UTF-8') . '</code></pre>';
        }
    }
    return $html;
}

$posts = [
    [1, 1, 'The quiet architecture behind fast PHP applications', 'how-to-build-a-modern-php-website', 'Speed is rarely a plugin. It is a set of boring decisions made before the first feature ships.', 1, 0, 1840, [1, 12], article([
        ['p', 'A fast PHP site is usually a small one. Not small in ambition, small in the number of times it touches the database, the disk, and the network before a reader sees a headline.'],
        ['h2', 'Start with the query, not the cache'],
        ['p', 'Teams reach for Redis because a page feels slow, then discover the page was running the same lookup twelve times. Count the queries on the homepage before you add a cache key. If a section needs three posts per category, fetch them together.'],
        ['p', 'Prepared statements are not only a security control. They also make it obvious which values are data and which values are structure. Keep limits and offsets as integers you interpolate after casting. Bind everything a visitor typed.'],
        ['ul', ['Index the columns you sort and filter on.', 'Select only the columns a card needs.', 'Attach tags in one follow-up query, not one query per card.']],
        ['quote', 'If the homepage makes a request for every card, the design is more expensive than it looks.'],
        ['h2', 'Render less, carefully'],
        ['p', 'Images should declare their size, lazy-load below the fold, and live behind a cache header. The article itself can stay dynamic. Readers notice a late hero image more than they notice a perfectly cached footer.'],
        ['code', "\$stmt = \$pdo->prepare('SELECT id, title, slug FROM posts WHERE status = ? LIMIT 6');\n\$stmt->execute(['published']);"],
    ])],
    [3, 1, 'What serious teams get wrong about AI assistants', 'what-serious-teams-get-wrong-about-ai', 'The useful question is not whether the model is clever. It is which decisions you still want a person to own.', 1, 1, 2210, [3, 6], article([
        ['p', 'Most AI rollouts fail in the same place: a team automates a task nobody had defined. The assistant produces confident paragraphs, the review step disappears, and the work gets worse while the dashboard says adoption is up.'],
        ['h2', 'Keep a human on the last mile'],
        ['p', 'Drafting is a good job for a model. Publishing is not. A newsroom can ask for a headline list, an outline, or a tighter lede, then an editor still has to decide what is true and what is merely plausible.'],
        ['ul', ['Write down the decision the tool is not allowed to make.', 'Store the source of every claim a draft relies on.', 'Measure correction time, not just generation time.']],
        ['quote', 'An assistant that cannot be disagreed with is just a faster way to be wrong.'],
        ['p', 'The teams that get value treat the model as a junior colleague with a short memory. They give it a narrow brief, they check the work, and they keep the byline attached to a person.'],
    ])],
    [2, 1, 'A practical SEO checklist for editorial sites', 'practical-seo-checklist-for-editorial-sites', 'Search traffic still comes from pages that are specific, fast, and unwilling to compete with themselves.', 0, 1, 1633, [4, 12], article([
        ['p', 'Editorial SEO is less mysterious than the plugin market suggests. Each story needs one URL, one title that a person would say out loud, and a description that matches the piece.'],
        ['h2', 'One address per story'],
        ['p', 'Duplicate paths are how publications leak authority. If a story lives at /blog/the-slug, it should not also answer at /post.php?id=14. Canonical tags help. Redirects are better. Clean routes are best.'],
        ['ul', ['Write a unique title and meta description.', 'Add article and breadcrumb structured data.', 'Put real pages in the sitemap and keep drafts out.', 'Paginate with stable URLs.']],
        ['p', 'Internal links should point at the category, the author, and two related stories a reader might actually want. A block of forty tag links is not a strategy.'],
    ])],
    [4, 2, 'Why your startup does not need another dashboard', 'why-your-startup-does-not-need-another-dashboard', 'A new chart rarely fixes a fuzzy decision. Most early teams need fewer numbers and a shorter meeting.', 0, 0, 980, [5, 6], article([
        ['p', 'The second dashboard is usually a sign that the first one did not change anyone\'s behavior. Founders add a chart when they are afraid to pick a priority.'],
        ['h2', 'Decide what would change your week'],
        ['p', 'If a metric cannot change what you ship on Monday, it does not belong on the wall. Revenue, activation, and the one operational number that keeps the product honest are enough for a long time.'],
        ['quote', 'A dashboard is a report. A decision is a meeting you were willing to end.'],
        ['p', 'Build the instrument after the question is stable. Until then, a spreadsheet and a written weekly note will tell you more than a wall of charts nobody opens.'],
    ])],
    [1, 2, 'The case for boring business models', 'the-case-for-boring-business-models', 'Subscriptions, services, and unglamorous margins still beat a story that only works in a pitch.', 1, 0, 1422, [6, 5], article([
        ['p', 'Boring is not the same as small. A company that invoices the same customers every month can plan. A company that needs a new story every quarter cannot.'],
        ['h2', 'Margin is a product decision'],
        ['p', 'The model shows up in the writing, the hiring, and the support queue. If the only way the numbers work is a future price increase nobody has agreed to, the model is a wish.'],
        ['ul', ['Know who pays and why they stay.', 'Prefer revenue you can explain without a footnote.', 'Leave room for support, because trust is part of the cost.']],
        ['p', 'Readers can smell a business that exists to be acquired. The more interesting companies can describe a customer, a price, and a reason the relationship continues.'],
    ])],
    [4, 4, 'How founders should read a term sheet', 'how-founders-should-read-a-term-sheet', 'The valuation is the loud number. Control, preference, and the option pool are the ones that change your outcome.', 1, 1, 1765, [9, 5], article([
        ['p', 'A term sheet is a short document with a long memory. Founders fixate on the headline valuation because it is easy to repeat. The clauses around it decide who gets paid, and when.'],
        ['h2', 'Read in this order'],
        ['ul', ['Liquidation preference and participation.', 'Who sits on the board.', 'The option pool, and whether it comes out of the pre-money number.', 'Protective provisions that can block a later sale.']],
        ['p', 'None of this is a reason to avoid outside capital. It is a reason to read slowly and to pay a lawyer who has seen the movie before. A friendly investor will not be offended by a careful founder.'],
        ['quote', 'If you cannot explain the preference stack on a whiteboard, you do not know what you sold.'],
    ])],
    [5, 4, 'Personal cash flow for people who hate spreadsheets', 'personal-cash-flow-without-the-spreadsheet', 'You do not need a twelve-tab workbook. You need a number for what must be paid and a number for what can wait.', 0, 0, 860, [9, 7], article([
        ['p', 'Personal finance advice often starts with a system elaborate enough to become a hobby. The useful version is smaller: money in, money that has a job, and money that does not.'],
        ['h2', 'Three piles are enough'],
        ['p', 'One account pays the rent and the bills that would hurt if they slipped. One account is for the life you actually live. One account is for later, and you do not borrow from it because a weekend looked expensive.'],
        ['p', 'Automate the transfers the day you are paid. The spreadsheet can wait until you are curious. The habit matters more than the template.'],
    ])],
    [3, 3, 'Designing a morning that survives real life', 'designing-a-morning-that-survives-real-life', 'The perfect routine collapses the first time a child, a train, or a deploy does not cooperate.', 0, 0, 740, [7], article([
        ['p', 'Morning routines are sold as personality. They work better as a short list of things that still happen when the day is rude.'],
        ['h2', 'Protect one block, not the whole hour'],
        ['p', 'Pick a single action that makes the rest of the day less fragile. For some people that is writing before messages. For others it is a walk before the first meeting. The ritual can be twenty minutes. It cannot depend on silence, a special notebook, and nobody needing you.'],
        ['quote', 'A routine you can keep on a bad Tuesday is the only one you have.'],
        ['p', 'Leave the rest of the morning flexible. The point is not to become a person in a video. The point is to arrive at the work already slightly on your own side.'],
    ])],
    [4, 5, 'The art of leaving a city you love', 'the-art-of-leaving-a-city-you-love', 'Remote work made it possible to go. It did not make the goodbye any cleaner.', 0, 1, 1190, [8], article([
        ['p', 'Leaving a city is a practical project disguised as a feeling. You can book the flight and still be wrong about what you will miss.'],
        ['h2', 'Take the map, not the myth'],
        ['p', 'Write down the places you actually use: the morning walk, the friend who will not move, the doctor, the desk by the window. A city is a set of repeating errands. If the next place cannot hold the errands that matter, the skyline will not save it.'],
        ['p', 'Give yourself a return date even if you do not use it. People leave more honestly when the door is not slammed.'],
    ])],
    [5, 5, 'A field guide to a slow weekend in Lisbon', 'a-slow-weekend-in-lisbon', 'Skip the checklist. The city is better as a sequence of neighborhoods than as a stack of monuments.', 0, 0, 690, [8, 7], article([
        ['p', 'Lisbon rewards the traveler who picks a hill and stays with it. The light changes, the trams complain, and lunch runs long if you let it.'],
        ['h2', 'A modest route'],
        ['ul', ['Morning coffee in a neighborhood you can walk without a map.', 'One museum, not three.', 'An afternoon by the river when the wind is up.', 'Dinner close enough to walk home.']],
        ['p', 'The mistake is treating the city as a trophy. It is a place to be slightly late. Pack shoes you can actually wear on tiled hills, and leave one meal unplanned.'],
    ])],
    [2, 2, 'Remote work is a management problem', 'remote-work-is-a-management-problem', 'The tool was never the hard part. Unclear ownership still shows up, it just shows up in a different window.', 0, 1, 1544, [8, 6], article([
        ['p', 'Companies blamed video calls for a problem they had in the office: nobody knew who was allowed to decide. Remote work made the gap visible because there was no hallway to patch it.'],
        ['h2', 'Write the work down'],
        ['p', 'A distributed team needs a record of what good looks like, when something is due, and who is waiting. That is not bureaucracy. It is how you avoid a meeting whose only purpose is to reconstruct the last meeting.'],
        ['quote', 'If the plan lives in someone\'s head, the company is closed whenever that person is asleep.'],
        ['p', 'Meet when a decision needs a voice. Write when the work needs a memory. The teams that do both stop arguing about whether remote work is real.'],
    ])],
    [3, 1, 'JavaScript without the framework hangover', 'javascript-without-the-framework-hangover', 'A publication does not need a client-side application to show an essay. It needs a page that loads.', 0, 1, 1330, [2, 12], article([
        ['p', 'The modern default is to begin with a framework and then spend a year removing it from the critical path. For a reading site, start from the other end.'],
        ['h2', 'Use the platform'],
        ['p', 'Forms can post. Fetch can enhance them. A navigation menu can be a button and a hidden element. None of that requires a build step, and all of it still works when the script is slow.'],
        ['ul', ['Keep the article in HTML.', 'Enhance search, comments, and the newsletter.', 'Do not block the first paint on a bundle.']],
        ['p', 'Frameworks earn their place when the interface is an application. A story is not an application. Treat it like a document and the page gets quieter.'],
    ])],
    [4, 1, 'Security habits every small team can afford', 'security-habits-every-small-team-can-afford', 'You do not need a security department to stop the mistakes that actually take sites down.', 0, 0, 2011, [11, 1], article([
        ['p', 'Small teams get breached in ordinary ways: a reused password, a file upload that was trusted, a query built with a string. The fixes are dull and they work.'],
        ['h2', 'A short list that holds'],
        ['ul', ['Hash passwords with password_hash and verify them with password_verify.', 'Bind every query. Escape every string you print.', 'Regenerate the session when someone signs in.', 'Reject uploads that are not images, then re-encode the ones that are.', 'Put a token on every form that changes data.']],
        ['p', 'Rate-limit the sign-in form. Log the exception. Do not show the stack trace to a stranger. None of this is glamorous, which is why it gets skipped until the incident report.'],
        ['quote', 'Security that depends on remembering to be careful will fail on a Friday.'],
    ])],
    [3, 3, 'Building a design system people actually use', 'building-a-design-system-people-actually-use', 'A system is a set of decisions you stop relitigating. If nobody can find them, you do not have one.', 0, 0, 910, [10, 7], article([
        ['p', 'Design systems fail when they are museums. The colors exist, the components exist, and every new page invents a third button because the first two were hard to find.'],
        ['h2', 'Name the few things that repeat'],
        ['p', 'A publication needs type, space, a card, a button, and a form field. Document those with the real CSS variables. Add a component only after the second team asks for it.'],
        ['p', 'Put the primary color in one place. If changing it means a hunt through a stylesheet, the system is a folder of screenshots.'],
    ])],
    [1, 2, 'The newsletter is not dead. The lazy one is.', 'the-newsletter-is-not-dead', 'People still open letters that sound like a person. They ignore the ones that sound like a funnel.', 0, 1, 1288, [4, 5], article([
        ['p', 'Inbox advice swings between funeral and gold rush. The truth is plainer. A letter with a point of view still gets read. A letter that exists to feed an ad slot does not.'],
        ['h2', 'Write it like a note'],
        ['p', 'Pick a cadence you can keep. Say what you published and why it mattered. Leave the twelve-sponsor template for someone else. The signup form should promise exactly that, and the admin should be able to export the list without a ceremony.'],
        ['quote', 'Frequency is a promise. Miss it on purpose or keep it. Do not apologize every week for a product you did not plan.'],
        ['p', 'Measure replies, not just opens. A list that argues with you is healthier than a list that never writes back.'],
    ])],
];

$out = [];
$out[] = "-- Meridian sample data";
$out[] = "SET NAMES utf8mb4;";
$out[] = "SET FOREIGN_KEY_CHECKS = 0;";
$out[] = "TRUNCATE TABLE rate_limits;";
$out[] = "TRUNCATE TABLE remember_tokens;";
$out[] = "TRUNCATE TABLE password_resets;";
$out[] = "TRUNCATE TABLE login_attempts;";
$out[] = "TRUNCATE TABLE activity_logs;";
$out[] = "TRUNCATE TABLE menu_items;";
$out[] = "TRUNCATE TABLE menus;";
$out[] = "TRUNCATE TABLE post_views;";
$out[] = "TRUNCATE TABLE contact_messages;";
$out[] = "TRUNCATE TABLE newsletter_subscribers;";
$out[] = "TRUNCATE TABLE comments;";
$out[] = "TRUNCATE TABLE media;";
$out[] = "TRUNCATE TABLE post_tags;";
$out[] = "TRUNCATE TABLE posts;";
$out[] = "TRUNCATE TABLE tags;";
$out[] = "TRUNCATE TABLE categories;";
$out[] = "TRUNCATE TABLE settings;";
$out[] = "TRUNCATE TABLE users;";
$out[] = "TRUNCATE TABLE roles;";
$out[] = "SET FOREIGN_KEY_CHECKS = 1;";

$roles = [
    [1, 'Super Admin', '["*"]'],
    [2, 'Admin', '["dashboard","posts.*","categories.*","tags.*","media.*","comments.*","users.*","newsletter.*","messages.*","menus.*","settings.*","profile"]'],
    [3, 'Editor', '["dashboard","posts.*","categories.*","tags.*","media.*","comments.*","profile"]'],
    [4, 'Author', '["dashboard","posts.own","media.own","comments.own","profile"]'],
    [5, 'User', '["profile"]'],
];
foreach ($roles as [$id, $name, $perms]) {
    $out[] = "INSERT INTO roles (id, name, permissions, created_at) VALUES ({$id}, " . q($name) . ", " . q($perms) . ", NOW());";
}

$users = [
    [1, 'Elena Voss', 'elena', 'elena@meridian.test', 1, '/assets/images/avatars/elena.jpg', 'Elena is the editor of Meridian. She writes about companies that last and the systems underneath them.', 'https://meridian.test', 'elenavoss', 'https://www.linkedin.com/in/elena-voss'],
    [2, 'Marcus Hale', 'marcus', 'marcus@meridian.test', 2, '/assets/images/avatars/marcus.jpg', 'Marcus runs the newsroom desk and edits the business and technology sections.', 'https://meridian.test', 'marcushale', 'https://www.linkedin.com/in/marcus-hale'],
    [3, 'Priya Raman', 'priya', 'priya@meridian.test', 3, '/assets/images/avatars/priya.jpg', 'Priya edits reported features and writes about software, design, and the tools people actually keep.', 'https://meridian.test', 'priyaraman', 'https://www.linkedin.com/in/priya-raman'],
    [4, 'Jonah Ellis', 'jonah', 'jonah@meridian.test', 4, '/assets/images/avatars/jonah.jpg', 'Jonah covers founders, money, and the practical side of leaving a job to build something.', 'https://meridian.test', 'jonahellis', 'https://www.linkedin.com/in/jonah-ellis'],
    [5, 'Amira Solt', 'amira', 'amira@meridian.test', 4, '/assets/images/avatars/amira.jpg', 'Amira writes about travel, attention, and the small systems that make a week feel deliberate.', 'https://meridian.test', 'amirasolt', 'https://www.linkedin.com/in/amira-solt'],
];
foreach ($users as [$id, $name, $username, $email, $role, $avatar, $bio, $website, $twitter, $linkedin]) {
    $out[] = "INSERT INTO users (id, name, username, email, password, role_id, avatar, bio, website, twitter, linkedin, status, created_at) VALUES ({$id}, " . q($name) . ", " . q($username) . ", " . q($email) . ", " . q($hash) . ", {$role}, " . q($avatar) . ", " . q($bio) . ", " . q($website) . ", " . q($twitter) . ", " . q($linkedin) . ", 'active', DATE_SUB(NOW(), INTERVAL 120 DAY));";
}

$categories = [
    [1, 'Technology', 'technology', 'Software, infrastructure, and the tools reshaping how work gets done.', 'Technology stories', 'Reporting on software, security, and the practice of building for the web.'],
    [2, 'Business', 'business', 'Companies, management, and the models that survive contact with customers.', 'Business', 'Essays on companies, leadership, and durable business models.'],
    [3, 'Lifestyle', 'lifestyle', 'Attention, routines, and the design of an ordinary week.', 'Lifestyle', 'Notes on attention, craft, and daily life.'],
    [4, 'Finance', 'finance', 'Money decisions for founders and for people who would rather not live in a spreadsheet.', 'Finance', 'Clear writing on cash, capital, and personal money.'],
    [5, 'Travel', 'travel', 'Cities, leaving them, and the weekends that are worth the train.', 'Travel', 'Field notes from cities and slow weekends.'],
];
foreach ($categories as [$id, $name, $slug, $description, $meta, $metaDescription]) {
    $out[] = "INSERT INTO categories (id, name, slug, description, meta_title, meta_description, status, created_at) VALUES ({$id}, " . q($name) . ", " . q($slug) . ", " . q($description) . ", " . q($meta) . ", " . q($metaDescription) . ", 'active', DATE_SUB(NOW(), INTERVAL 200 DAY));";
}

$tags = ['PHP', 'JavaScript', 'AI', 'SEO', 'Startups', 'Leadership', 'Productivity', 'Remote Work', 'Investing', 'Design', 'Cybersecurity', 'Web Development'];
foreach ($tags as $i => $name) {
    $slug = strtolower(str_replace(' ', '-', $name));
    $out[] = 'INSERT INTO tags (id, name, slug, created_at) VALUES (' . ($i + 1) . ', ' . q($name) . ', ' . q($slug) . ', NOW());';
}

foreach ($posts as $i => $post) {
    [$author, $category, $title, $slug, $excerpt, $featured, $trending, $views, $tagIds, $content] = $post;
    $id = $i + 1;
    $days = [2, 4, 6, 9, 12, 15, 18, 21, 25, 28, 32, 36, 40, 46, 52][$i];
    $minutes = max(1, (int) ceil(str_word_count(strip_tags($content)) / 220));
    $image = sprintf('/assets/images/covers/%02d.jpg', $id);
    $out[] = "INSERT INTO posts (id, author_id, category_id, title, slug, excerpt, content, featured_image, featured_image_alt, status, visibility, is_featured, is_trending, views, reading_time, meta_title, meta_description, published_at, created_at) VALUES ({$id}, {$author}, {$category}, " . q($title) . ", " . q($slug) . ", " . q($excerpt) . ", " . q($content) . ", " . q($image) . ", " . q($title) . ", 'published', 'public', {$featured}, {$trending}, {$views}, {$minutes}, " . q($title) . ", " . q($excerpt) . ", DATE_SUB(NOW(), INTERVAL {$days} DAY), DATE_SUB(NOW(), INTERVAL {$days} DAY));";
    foreach ($tagIds as $tagId) {
        $out[] = "INSERT INTO post_tags (post_id, tag_id) VALUES ({$id}, {$tagId});";
    }
}

$comments = [
    [1, null, 'Lena Ortiz', 'lena@example.com', 'This is the clearest explanation of query cost I have handed to a new hire.', 'approved', null, 1],
    [1, 1, 'Elena Voss', 'elena@meridian.test', 'Glad it was useful. The follow-up we cut was a whole section on opcode caches.', 'approved', 1, 1],
    [1, null, 'Chris Adeyemi', 'chris@example.com', 'We found twelve duplicate lookups on our homepage after reading this.', 'approved', null, 2],
    [2, null, 'Nora Blake', 'nora@example.com', 'The line about correction time should be taped to every rollout plan.', 'approved', null, 3],
    [2, null, 'Samir Shah', 'samir@example.com', 'We automated a task we had never written down. Painfully accurate.', 'approved', null, 4],
    [3, null, 'Ivy Chen', 'ivy@example.com', 'The canonical section matches a mess we are still cleaning up.', 'approved', null, 5],
    [3, 2, 'Marcus Hale', 'marcus@meridian.test', 'If you have two URLs, pick one and redirect. Do not leave both to rot.', 'approved', 6, 5],
    [4, null, 'Greta Holm', 'greta@example.com', 'We killed a dashboard last month. Nobody asked for it back.', 'approved', null, 6],
    [5, null, 'Owen Park', 'owen@example.com', 'Boring models are a hard sell in a pitch room and an easy life afterward.', 'approved', null, 8],
    [6, null, 'Helena Costa', 'helena@example.com', 'The option pool note saved us from a sloppy verbal agreement.', 'approved', null, 9],
    [6, null, 'Spam Bot', 'spam@example.com', 'Buy followers now http://spam.example', 'spam', null, 2],
    [7, null, 'Ruth Keller', 'ruth@example.com', 'Three accounts is the first system I have kept for more than a month.', 'approved', null, 10],
    [8, null, 'Diego Marquez', 'diego@example.com', 'I stopped trying to win the morning and the week got kinder.', 'approved', null, 11],
    [9, null, 'Farah Nouri', 'farah@example.com', 'The return-date idea is the part I needed.', 'approved', null, 12],
    [10, null, 'Pauline Berg', 'pauline@example.com', 'Taking this route in October. One museum only. Promise.', 'approved', null, 14],
    [11, null, 'Victor Lang', 'victor@example.com', 'Our all-hands was a reconstruction project. Writing it down helped.', 'approved', null, 7],
    [12, null, 'Mina Cho', 'mina@example.com', 'Shipping a reading site without a client framework still feels like a small rebellion.', 'approved', null, 6],
    [13, null, 'Anonymous Reader', 'reader@example.com', 'Please cover how you store reset tokens next.', 'pending', null, 1],
    [14, null, 'Joel Adebayo', 'joel@example.com', 'We documented four components and stopped inventing buttons. It worked.', 'approved', null, 15],
    [15, null, 'Hannah Weiss', 'hannah@example.com', 'I reply to this letter. That is the whole review.', 'approved', null, 4],
    [15, null, 'Pending Person', 'pending@example.com', 'Does the export include unsubscribed addresses?', 'pending', null, 1],
];
foreach ($comments as $i => [$postId, $userId, $name, $email, $content, $status, $parent, $days]) {
    $uid = $userId === null ? 'NULL' : (string) $userId;
    $pid = $parent === null ? 'NULL' : (string) $parent;
    $id = $i + 1;
    $out[] = "INSERT INTO comments (id, post_id, user_id, name, email, content, status, parent_id, ip_hash, created_at) VALUES ({$id}, {$postId}, {$uid}, " . q($name) . ", " . q($email) . ", " . q($content) . ", " . q($status) . ", {$pid}, " . q(hash('sha256', 'seed-' . $id)) . ", DATE_SUB(NOW(), INTERVAL {$days} DAY));";
}

$subscribers = ['ada@example.com','ben@example.com','chloe@example.com','dan@example.com','esme@example.com','felix@example.com','gia@example.com','hugo@example.com','iris@example.com','jade@example.com'];
foreach ($subscribers as $i => $email) {
    $status = $i === 9 ? 'unsubscribed' : 'active';
    $out[] = "INSERT INTO newsletter_subscribers (email, status, created_at) VALUES (" . q($email) . ", " . q($status) . ", DATE_SUB(NOW(), INTERVAL " . (3 + $i) . " DAY));";
}

$messages = [
    ['Noah Grant', 'noah@example.com', 'Correction on the term sheet piece', 'The preference example in paragraph four could use a clearer number. Happy to share the note.', 'unread'],
    ['Studio North', 'hello@studionorth.example', 'Partnership', 'We publish a small print journal and would like to talk about a shared issue.', 'read'],
    ['Leah Ortiz', 'leah@example.com', 'Newsletter removal', 'Please remove leah.old@example.com if it is still on the list.', 'unread'],
];
foreach ($messages as $i => $message) {
    $out[] = "INSERT INTO contact_messages (name, email, subject, message, status, created_at) VALUES (" . q($message[0]) . ", " . q($message[1]) . ", " . q($message[2]) . ", " . q($message[3]) . ", " . q($message[4]) . ", DATE_SUB(NOW(), INTERVAL " . ($i + 1) . " DAY));";
}

$settings = [
    'site_name' => 'Meridian',
    'site_description' => 'An independent publication on technology, business, and modern work.',
    'primary_color' => '#F05A2A',
    'email' => 'desk@meridian.test',
    'phone' => '+1 (415) 555-0148',
    'address' => '418 Market Street, Suite 600, San Francisco, CA',
    'facebook' => 'https://facebook.com/meridianjournal',
    'instagram' => 'https://instagram.com/meridianjournal',
    'twitter' => 'meridianjournal',
    'linkedin' => 'https://www.linkedin.com/company/meridian-journal',
    'youtube' => 'https://www.youtube.com/@meridianjournal',
    'footer_text' => 'Meridian publishes original reporting and essays. No recycled roundups.',
    'ga_id' => '',
    'gsc_verification' => '',
    'seo_title' => 'Meridian — Stories worth reading',
    'seo_description' => 'Meridian is an independent publication covering technology, business, finance, lifestyle, and travel.',
    'logo' => '',
    'favicon' => '',
];
foreach ($settings as $key => $value) {
    $out[] = "INSERT INTO settings (setting_key, setting_value) VALUES (" . q($key) . ", " . q($value) . ");";
}

$out[] = "INSERT INTO menus (id, name, location) VALUES (1, 'Header', 'header'), (2, 'Footer', 'footer');";
$header = [['Journal', '/blog', 1], ['Technology', '/category/technology', 2], ['Business', '/category/business', 3], ['Finance', '/category/finance', 4], ['About', '/about', 5]];
$footer = [['About', '/about', 1], ['Contact', '/contact', 2], ['Privacy', '/privacy', 3], ['Terms', '/terms', 4], ['Sitemap', '/sitemap.xml', 5]];
foreach ($header as [$label, $url, $sort]) {
    $out[] = "INSERT INTO menu_items (menu_id, label, url, sort_order) VALUES (1, " . q($label) . ", " . q($url) . ", {$sort});";
}
foreach ($footer as [$label, $url, $sort]) {
    $out[] = "INSERT INTO menu_items (menu_id, label, url, sort_order) VALUES (2, " . q($label) . ", " . q($url) . ", {$sort});";
}

$out[] = "INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES
(1, 'post.publish', 'Published the latest briefing', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 'comment.approved', 'Approved a reader comment', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 'settings.update', 'Updated the footer note', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 'post.create', 'Filed a draft from the road', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(1, 'user.create', 'Invited a contributing author', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 20 DAY));";

for ($day = 0; $day < 14; $day++) {
    $count = [4, 6, 3, 8, 5, 9, 7, 4, 11, 6, 8, 5, 10, 7][$day];
    for ($n = 0; $n < $count; $n++) {
        $postId = ($n % 15) + 1;
        $hash = hash('sha256', "view-{$day}-{$n}");
        $out[] = "INSERT INTO post_views (post_id, ip_hash, user_agent, created_at) VALUES ({$postId}, " . q($hash) . ", 'Mozilla/5.0 seed', DATE_SUB(NOW(), INTERVAL {$day} DAY));";
    }
}

foreach ($posts as $i => $post) {
    $id = $i + 1;
    $out[] = "INSERT INTO media (user_id, filename, original_name, filepath, mime_type, size, alt_text) VALUES (1, " . q(sprintf('%02d.jpg', $id)) . ", " . q($post[2]) . ", " . q(sprintf('/assets/images/covers/%02d.jpg', $id)) . ", 'image/jpeg', 80000, " . q($post[2]) . ");";
}

file_put_contents(__DIR__ . '/seed.sql', implode("\n", $out) . "\n");
echo "seed.sql written\n";
