<h3>SAMPLE SHOPPING PROJECT</h3>
<hr/>
Shopping project is a shopping basket/cart system with developed PHP-Symfony, MySQL, Redis,

<h3>Installation</h4>
<hr/>

<p>1. Clone project from Github</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>git clone git@github.com:takdemir/shopping.git</pre>
</div>

<p>2. Change directory to project folder</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>cd shopping</pre>
</div>

<p>3. Up project with docker command below</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>docker-compose -f docker-compose.yaml up -d</pre>
</div>

<p>4. Install vendors</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>composer install</pre>
</div>

<p>5. Don't send .env to repository but this is the sample project. 
Copy and paste .env and change the name of file with .env.local. 
Check .env.local file and be sure, APP_ENV in the .env.local is dev</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>cp .env .env.local</pre>
</div>

<p>6. Create database</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>php bin/console doctrine:database:create</pre>
</div>

