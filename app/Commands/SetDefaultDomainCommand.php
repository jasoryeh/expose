<?php

namespace App\Commands;

use App\Client\Support\DefaultDomainNodeVisitor;
use App\Client\Support\DefaultServerNodeVisitor;
use App\Client\Support\InsertDefaultDomainNodeVisitor;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser\Php7;
use PhpParser\PrettyPrinter\Standard;

class SetDefaultDomainCommand extends ExposeCommand
{
    protected $signature = 'default-domain {domain?} {--server=}';

    protected $description = 'Set or retrieve the default domain to use with Expose.';

    public function handle()
    {
        $domain = $this->argument('domain');
        $server = $this->option('server');
        if (! is_null($domain)) {
            $this->info('Setting the Expose default domain to "'.$domain.'"');

            $configFile = $this->getConfig();
            $updatedConfigFile = $this->modifyConfigurationFile($configFile, $domain, $server);
            file_put_contents($configFile, $updatedConfigFile);

            return;
        }

        if (is_null($domain = config('expose.default_domain'))) {
            $this->info('There is no default domain specified.');
        } else {
            $this->info('Current default domain: '.$domain);
        }
    }

    protected function modifyConfigurationFile(string $configFile, string $domain, ?string $server)
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $parser = new Php7($lexer);

        $oldStmts = $parser->parse(file_get_contents($configFile));
        $oldTokens = $lexer->getTokens();

        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor(new CloningVisitor());
        $newStmts = $nodeTraverser->traverse($oldStmts);

        $nodeFinder = new NodeFinder;

        $defaultDomainNode = $nodeFinder->findFirst($newStmts, function (Node $node) {
            return $node instanceof Node\Expr\ArrayItem && $node->key && $node->key->value === 'default_domain';
        });

        if (is_null($defaultDomainNode)) {
            $nodeTraverser = new NodeTraverser;
            $nodeTraverser->addVisitor(new InsertDefaultDomainNodeVisitor());
            $newStmts = $nodeTraverser->traverse($newStmts);
        }

        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor(new DefaultDomainNodeVisitor($domain));

        if (! is_null($server)) {
            $nodeTraverser->addVisitor(new DefaultServerNodeVisitor($server));
        }

        $newStmts = $nodeTraverser->traverse($newStmts);

        $prettyPrinter = new Standard();

        return $prettyPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }
}
