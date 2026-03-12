<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KamarResource\Pages;
use App\Filament\Resources\KamarResource\RelationManagers;
use App\Models\Kamar;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// tambahan
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload; //untuk tipe file

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class KamarResource extends Resource
{
    protected static ?string $model = Kamar::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //isikan dengan input type form
                Grid::make(1) // Membuat hanya 1 kolom
                ->schema([
                    TextInput::make('id')
                        ->label('ID Kamar')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('no_kamar')
                        ->label('No Kamar')
                        ->placeholder('KMR-001')
                        ->required(),

                    TextInput::make('nama_kamar')
                        ->label('Nama Kamar')
                        ->required(),

                    TextInput::make('lantai_kamar')
                        ->label('Lantai Kamar')
                        ->numeric()
                        ->required(),

                    FileUpload::make('foto_kamar')
                        ->label('Foto Kamar')
                        ->image()
                        ->directory('foto_kamar')
                        ->required(),

                    TextInput::make('harga_kamar')
                        ->label('Harga Kamar')
                        ->numeric()
                        ->required(),

                    Select::make('status_kamar')
                        ->label('Status Kamar')
                        ->options([
                            'Kosong' => 'Kosong',
                            'Terisi' => 'Terisi'
                        ])
                        ->required(),

                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //isikan kolom mana saja yang akan ditampilkan di sini
                TextColumn::make('id')->label('ID'),
                TextColumn::make('no_kamar')->label('No Kamar'),
                TextColumn::make('nama_kamar')->label('Nama Kamar'),
                TextColumn::make('lantai_kamar')->label('Lantai'),
                TextColumn::make('harga_kamar')->label('Harga'),
                ImageColumn::make('foto_kamar')->label('Foto'),
                TextColumn::make('status_kamar')->label('Status'),
            ])
            ->filters([
                //untuk membuat filter 
                Tables\Filters\SelectFilter::make('status_kamar')
                ->label('Status Kamar')
                ->options([
                    'Kosong' => 'Kosong',
                    'Terisi' => 'Terisi',
                ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKamars::route('/'),
            'create' => Pages\CreateKamar::route('/create'),
            'edit' => Pages\EditKamar::route('/{record}/edit'),
        ];
    }
}
