<h1 align="center">Schedule Bot Bul</h1>

<p align="center">
  Schedule Bot Bul is a bot designed to optimize and organize the workspace for learning. It helps manage tasks, create reminders, and integrates with Trello for efficient task tracking.
</p>

---

<h2>📋 Requirements</h2>

<ul>
  <li><strong>PHP</strong> >= 8.0</li>
  <li><strong>Composer</strong></li>
  <li><strong>MySQL</strong> or another supported database</li>
  <li><strong>Node.js</strong> and <strong>npm</strong></li>
  <li><strong>Telegram Bot API Token</strong></li>
  <li><strong>Trello API Key</strong></li>
</ul>

---

<h2>🚀 Installation</h2>

<ol>
  <li>
    <strong>Clone the repository</strong>
    <pre><code>git clone https://github.com/jeashes/schedule-bot-bul.git  
cd schedule-bot-bul</code></pre>
  </li>

  <li>
    <strong>Install dependencies</strong>
    <pre><code>composer install  
npm install</code></pre>
  </li>

  <li>
    <strong>Set up environment variables</strong>
    <p>Copy the <code>.env.example</code> file to <code>.env</code> and update it with your credentials:</p>
    <pre><code>cp .env.example .env</code></pre>
    <p>Edit the <code>.env</code> file and add the following:</p>
    <pre><code>TELEGRAM_BOT_API_TOKEN=your_telegram_bot_token 
TELEGRAM_BOT_WEBHOOK=your_telegram_bot_webhook
TELEGRAM_BOT_USERNAME=your_telegram_bot_username 


<pre><code>TRELLO_API_KEY=your_trello_api_key
TRELLO_API_SECRET=your_trello_secret
TRELLO_API_TOKEN=your_api_token
TRELLO_ORGANIZATION_ID=your_organization_id

DB_CONNECTION=mysql  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE=schedule_bot  
DB_USERNAME=root  
DB_PASSWORD=your_database_password</code></pre>
  </li>

  <li>
    <strong>Generate the application key and migrate the database</strong>
    <pre><code>php artisan key:generate  
php artisan migrate</code></pre>
  </li>

  <li>
    <strong>Run the bot</strong>
    <p>Start the Laravel development server and queue worker:</p>
    <pre><code>php artisan serve  
php artisan queue:work</code></pre>
  </li>
</ol>

---

<h2>💡 Usage</h2>

<p>Once the bot is running, you can interact with it via Telegram. It will help you manage tasks, set reminders, and sync with Trello for seamless task tracking.</p>

---

<h2>🤝 Contributing</h2>

<p>Contributions are welcome! Please fork the repository and submit a pull request with your changes.</p>

---

<h2>📄 License</h2>

<p>This project is licensed under the MIT License. See the <a href="LICENSE">LICENSE</a> file for details.</p>
