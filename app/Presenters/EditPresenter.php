<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Explorer;

class EditPresenter extends Presenter
{
    public function __construct(private Explorer $database)
    {
    }

    public function renderEdit(int $postId): void
    {
        $post = $this->database
            ->table('posts')
            ->get($postId);

        if (!$post) {
            $this->error('Post not found');
        }

        $this->getComponent('postForm')
            ->setDefaults($post->toArray());
    }

    public function startup(): void
    {
        parent::startup();

        if(!$this->getUser()->isLoggedIn()){
            $this->redirect('Sign:in');
        }
    }

    protected function createComponentPostForm(): Form
    {
        $form = new Form();
        $form->addText('title', 'Titulek:')
            ->setRequired();
        $form->addTextArea('content', 'Obsah:')
            ->setRequired();

        $form->addSubmit('send', 'Uložit a publikovat');
        $form->onSuccess[] = $this->postFormSucceeded(...);

        return $form;
    }

    private function postFormSucceeded(array $data): void
    {
        $postId = $this->getParameter('postId');

        if($postId){
            $post = $this->database
                ->table('posts')
                ->get($postId);
            $post->update($data);
        } else {
            $post = $this->database
                ->table('posts')
                ->insert($data);
        }

        $this->flashMessage('Příspěvek byl úspěšně publikován.', 'success');
        $this->redirect('Post:show', $post->id);
    }
}