<h3>About</h3>
Psums project is created to analyze lorem ipsum stream by applying pre defined rules<br>
It is composed of 3 services<br><br>
<ol>
    <li><a href="https://github.com/zus1/psums_aggregator">Aggregator</a></li>
    <li><a href="https://github.com/zus1/psums_streams">Streams</a></li>
    <li><a href="https://github.com/zus1/psums-api">Api</a></li>
</ol>

<h3>Installation for Psums project</h3>
Psums can be installed in two different ways

First is to pull all three services and put them in same directory (each service in its own directory
and then all put in same parent directory). Then go to Aggregator directory and run
<pre><code>docker-compose up</code></pre>
This will build up all containers, and those are following:
<ul>
    <li>psums_aggregator</li>
    <li>psums_streams</li>
    <li>psums_api</li>
    <li>psums_mysql</li>
    <li>psums_memcached</li>
</ul>
After build process i completed (may take a few minutes, depending if you have some images built already)
it necessary to bash into aggregator container and run migrations. Psums uses Phinx as migration engine.
<br><br>
<pre><code>docker container exec -it bash psums_aggregator</code></pre>
Yous should be in /var/www/html direcotry, now run migrations
<br><br>
<pre><code>php library/phinx/bin/phinx migrate</code></pre>
Now you should be all set up
<br><br>

Second way is by using <a href="https://github.com/zus1/psums_compose">Psums composer</a>, you can follow instalation instructions on that repository. 
It will install production version without access to code base

<h3>How to use aggregator</h3>
It pretty much uses itself :) Entire process is automated with crons. What aggregator dose is
take input streams sent by psums_streams service, parses them and saves word stream to database. Thats the 
fist part of his code base, saving input streams. Second part is applying pre defined rules to that stream and saving
results. And that is, simple right. Those results will then be used by psums_api service to be exposed i format
of REST api response.<br><br>
Aggregator comes with 4 rules out of box. Those are:
<br><br>
<ol>
    <li><b>compare_vowels:</b> This rule counts number of vowels in words and makes comparison between streams</li>
    <li><b>pook_beef:</b> Funy as it sounds, this rule counts occurrences of pook in first stream and beef in second one, and compares them</li>
    <li><b>pattern</b>: Uses provided patter to check occurrence of symbols in both streams, and compare</li>
    <li><b>match_making</b>: For all words pairs in pattern, checks both steams and tries to first word in first stream and second word in second stream</li>
</ol>
But is easily extendable with new rules. Process for that is add rule to rules_available table and add rule logic
to aggregator project, and that's it. Rules logic is build to be easily extendable

<h3>Tracking, whats going on?</h3>
Aggregator has multiple logs that will enable you to track whats happening with incoming streams and applying rules.
You can check out <b>log_streams</b> table for input streams and <b>log</b> table will keep record of any exceptions if the happen.
To si final reports of each cycle look into corns.log in aggregator container. So bash into container, go to /var/log directory
and run<br><br>
<pre><code>tail -f cron.log</code></pre>
This will tail the log and track the inputs in command line.<br><br>
Results of rules cycles will be saved to <b>rules_results</b> table<br>

If you wish to run included unit test, it can be done by running following command from /var/www/html directory
<pre><code>php vendor/phpunit/phpunit/phpunit tests</code></pre>

And that's all she wroth, for other services docs check their respected repositories, and as always have fun :)   

