## Предназначение

> PHARMAWEB-513 и другие задачи связанные с кнопкой "Копировать"

* Копирует рекурсивно по графу все связи Энтити для вставки в базу.
* Проверяет, если копия по этому айдишнику уже была сделана.
* Позволяет задать стратегию, как в вышеуказанной задаче "Скопировать Анкету, в анкете - фильтры и вопросы, в вопросах - варианты ответов, в вариантах ответов - енумы"
* Позволяет избежать рекурсии, когда мы получаем цикл на клонировании обьектов
* Позволяет задать idAllocator, чтобы выдавать айди по собственному правилу

## Запуск

* Зайти в папку /tests
* Запустить php -S localhost:3000
* Идти в браузер
