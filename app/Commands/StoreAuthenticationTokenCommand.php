<?php

namespace App\Commands;

use App\Client\Support\TokenNodeVisitor;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser\Php7;
use PhpParser\PrettyPrinter\Standard;

class StoreAuthenticationTokenCommand extends ExposeCommand
{
    protected $signature = 'token {token?}';

    protected $description = 'Set or retrieve the authentication token to use with Expose.';

    public function handle()
    {
        if (! is_null($this->argument('token'))) {
            $this->info('Setting the expose authentication token to "'.$this->argument('token').'"');

            $configFile = $this->getConfig();
            $updatedConfigFile = $this->modifyConfigurationFile($configFile, $this->argument('token'));
            file_put_contents($configFile, $updatedConfigFile);

            return;
        }

        if (is_null($token = config('expose.auth_token'))) {
            $this->info('There is no authentication token specified.');
        } else {
            $this->info('Current authentication token: '.$token);
        }
    }

    protected function modifyConfigurationFile(string $configFile, string $token)
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

        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor(new TokenNodeVisitor($token));

        $newStmts = $nodeTraverser->traverse($newStmts);

        $prettyPrinter = new Standard();

        return $prettyPrinter->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
    }
}
