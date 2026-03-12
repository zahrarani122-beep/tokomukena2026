<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangResource\Pages;
use App\Filament\Resources\BarangResource\RelationManagers;
use App\Models\Barang;
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
use Filament\Forms\Components\FileUpload; //untuk tipe file

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class BarangResource extends Resource
{
    protected static ?string $model = Barang::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Grid::make(1)->schema([

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
                        'Terisi' => 'Terisi',
                    ])
                    ->required(),

            ])
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('kode_barang')
                    ->searchable(),
                // agar bisa di search
                TextColumn::make('nama_barang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('harga_barang')
                    ->label('Harga Barang')
                    ->formatStateUsing(fn (string|int|null $state): string => rupiah($state))
                    ->extraAttributes(['class' => 'text-right']) // Tambahkan kelas CSS untuk rata kanan
                    ->sortable()
                ,
                ImageColumn::make('foto'),
                TextColumn::make('stok'),
                TextColumn::make('rating'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBarangs::route('/'),
            'create' => Pages\CreateBarang::route('/create'),
            'edit' => Pages\EditBarang::route('/{record}/edit'),
        ];
    }
}
