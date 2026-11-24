<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Les photos étaient stockées dans public/products/, ce qui créait un vrai dossier
 * portant le même nom que la route /products : le serveur web répondait 403.
 * On les déplace dans public/uploads/products/ et on met à jour les chemins.
 */
return new class extends Migration
{
    private string $oldDir = 'products';
    private string $newDir = 'uploads/products';

    public function up(): void
    {
        $this->moveFiles($this->oldDir, $this->newDir);
        $this->rewritePaths($this->oldDir, $this->newDir);

        // Supprimer l'ancien dossier s'il est vide, pour libérer la route /products
        $old = public_path($this->oldDir);
        if (File::isDirectory($old) && count(File::allFiles($old)) === 0) {
            File::deleteDirectory($old);
        }
    }

    public function down(): void
    {
        // On ne recrée pas public/products : cela casserait de nouveau la route /products.
    }

    private function moveFiles(string $from, string $to): void
    {
        $source = public_path($from);
        if (!File::isDirectory($source)) {
            return;
        }

        foreach (File::allFiles($source) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $target = public_path($to . '/' . $relative);

            File::ensureDirectoryExists(dirname($target), 0755);

            if (!File::exists($target)) {
                File::move($file->getPathname(), $target);
            } else {
                File::delete($file->getPathname());
            }
        }
    }

    private function rewritePaths(string $from, string $to): void
    {
        DB::table('products')
            ->where('photo', 'like', $from . '/%')
            ->orderBy('id')
            ->select(['id', 'photo'])
            ->chunkById(200, function ($rows) use ($from, $to) {
                foreach ($rows as $row) {
                    DB::table('products')
                        ->where('id', $row->id)
                        ->update(['photo' => $to . substr($row->photo, strlen($from))]);
                }
            });
    }
};
