<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Latte\Essential\Nodes;

use Latte\Compiler\Node;
use Latte\Compiler\Nodes;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\NodeTraverser;
use Latte\Compiler\PhpHelpers;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Compiler\Token;


/**
 * {templatePrint [ClassName]}
 */
class TemplatePrintNode extends StatementNode
{
	public ?string $template;


	public static function create(Tag $tag): static
	{
		$node = new static;
		$node->template = $tag->parser->stream->tryConsume(Token::Php_Identifier, Token::Php_NameFullyQualified, Token::Php_NameQualified)?->text;
		return $node;
	}


	public function print(PrintContext $context): string
	{
		return '(new Latte\Essential\Blueprint)->printClass($this, ' . PhpHelpers::dump($this->template) . '); exit;';
	}


	public function &getIterator(): \Generator
	{
		false && yield;
	}


	/**
	 * Pass: moves this node to head.
	 */
	public static function moveToHeadPass(Nodes\TemplateNode $templateNode): void
	{
		(new NodeTraverser)->traverse($templateNode->main, function (Node $node) use ($templateNode) {
			if ($node instanceof self) {
				array_unshift($templateNode->head->children, $node);
				return new Nodes\NopNode;
			}
		});
	}
}
