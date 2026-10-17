<?php

namespace App\Filament\Resources\Articles\Schemas;

use App\Services\ArticleMediaStorage;
use App\Models\Category;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Forms\Components\CheckboxList;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                    if (blank($get('slug'))) {
                        $set('slug', Str::slug($state ?? ''));
                    }
                }),
                TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
                TextInput::make('subtitle')->maxLength(255),
                Textarea::make('excerpt')->required()->rows(3)->maxLength(1000),
                RichEditor::make('body')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('r2_media')
                    ->fileAttachmentsDirectory('article-body')
                    ->fileAttachmentsVisibility('private')
                    ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->fileAttachmentsMaxSize(10240)
                    ->saveUploadedFileAttachmentUsing(fn (TemporaryUploadedFile $file): string => app(ArticleMediaStorage::class)->storeBodyAttachment($file))
                    ->preventFileAttachmentPathTampering(),
                Select::make('category_id')
                    ->label('Category')
                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                CheckboxList::make('tags')->relationship(titleAttribute: 'name')->columns(3),
                Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published'])->required()->default('draft'),
                DateTimePicker::make('published_at'),
                FileUpload::make('cover_image')
                    ->disk('r2_media')
                    ->directory('article-covers')
                    ->visibility('private')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(ArticleMediaStorage::class)->storeCover($file)),
                TextInput::make('cover_image_alt')->maxLength(255),
                TextInput::make('cover_image_caption')->maxLength(255),
                TextInput::make('seo_title')->maxLength(255),
                Textarea::make('seo_description')->rows(2)->maxLength(320),
            ]);
    }
}
