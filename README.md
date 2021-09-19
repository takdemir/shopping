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

<p>6. Create database
<br />
<u>VERY IMPORTANT!</u>
<br/>
If you want to execute DB processes in the <u>docker container</u>, please modify DATABASE_URL in .env.local.
Other DATABASE_URL is already written in the same file. 
</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>php bin/console doctrine:database:create</pre>
</div>

<p>7. Migration time. To create all tables and fill them with demo data, execute code below.</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>php bin/console doctrine:migration:migrate</pre>
</div>


<p>9. UserController test is written to be an example. 
You can execute it by code below</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>php bin/phpunit --testdox</pre>
</div>


<p>10. You can find API DOC link below
</p>
<div class="highlight highlight-source-shell position-relative">
    <pre>http://localhost:9041/api/doc</pre>
</div>

