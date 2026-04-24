<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenjualResource\Pages;
use App\Filament\Resources\PenjualResource\RelationManagers;
use App\Models\Penjual;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// untuk form dan table
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class PenjualResource extends Resource
{
    protected static ?string $model = Penjual::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    // merubah nama label menjadi Penjual
    protected static ?string $navigationLabel = 'Penjual';

    // tambahan buat grup masterdata
    protected static ?string $navigationGroup = 'Masterdata';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('kode_penjual')
                    ->default(fn () => Penjual::getKodePenjual()) // Ambil default dari method getKodePenjual
                    ->label('Kode Penjual')
                    ->required()
                    ->readonly() // Membuat field menjadi read-only
                ,
                TextInput::make('nama_penjual')
                    ->required()
                    ->placeholder('Masukkan nama penjual')
                ,
                TextInput::make('alamat')
                    ->required()
                    ->placeholder('Masukkan alamat penjual')
                ,
                TextInput::make('telepon')
                    ->required()
                    ->placeholder('Masukkan nomor telepon')
                    ->numeric()
                    ->prefix('+62')
                    ->extraAttributes(['pattern' => '^[0-9]+$', 'title' => 'Masukkan angka yang diawali dengan 0'])
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_penjual'),
                TextColumn::make('nama_penjual'),
                TextColumn::make('alamat'),
                TextColumn::make('telepon'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListPenjuals::route('/'),
            'create' => Pages\CreatePenjual::route('/create'),
            'edit' => Pages\EditPenjual::route('/{record}/edit'),
        ];
    }
}