<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Item;
use App\Enum\CategoryPublishStateEnum;
use App\Repository\CategoryRepository;
use App\Service\BreadcrumbsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('template/front/home/index.html.twig');
    }

    #[Route('/mock', name: 'mock')]
    public function generateKoreanCosmeticsCategoriesAndItems(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
// 1️⃣ Очищаем БД
        $entityManager->getConnection()->executeStatement('TRUNCATE TABLE public.item_category');

// Удаляем сущности (через DQL можно DELETE)
        $entityManager->createQuery('DELETE FROM App\Entity\Item')->execute();
        $entityManager->createQuery('DELETE FROM App\Entity\Category')->execute();

        // 2️⃣ Категории

        // Каталог
        $rootCategory = new Category();
        $rootCategory->setName('Каталог')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE);
        $entityManager->persist($rootCategory);
        $entityManager->flush();

        $rootCategory = $categoryRepository->findOneBy(['url' => '/catalog']);
        // Подготовим массив для удобства генерации товаров
        $categories = [];

        // Уход за лицом
        $faceCare = new Category();
        $faceCare->setName('Уход за лицом')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($rootCategory);
        $entityManager->persist($faceCare);

        $creams = new Category();
        $creams->setName('Кремы')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($faceCare);
        $entityManager->persist($creams);
        $categories[] = $creams;

        $masks = new Category();
        $masks->setName('Маски')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($faceCare);
        $entityManager->persist($masks);
        $categories[] = $masks;

        // Уход за телом
        $bodyCare = new Category();
        $bodyCare->setName('Уход за телом')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($rootCategory);
        $entityManager->persist($bodyCare);

        $lotions = new Category();
        $lotions->setName('Лосьоны')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($bodyCare);
        $entityManager->persist($lotions);
        $categories[] = $lotions;

        $scrubs = new Category();
        $scrubs->setName('Скрабы')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($bodyCare);
        $entityManager->persist($scrubs);
        $categories[] = $scrubs;

        // Мужская косметика
        $menCare = new Category();
        $menCare->setName('Мужская косметика')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($rootCategory);
        $entityManager->persist($menCare);

        $menFaceCleansing = new Category();
        $menFaceCleansing->setName('Очищение лица')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($menCare);
        $entityManager->persist($menFaceCleansing);
        $categories[] = $menFaceCleansing;

        $menFaceCreams = new Category();
        $menFaceCreams->setName('Кремы для лица')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($menCare);
        $entityManager->persist($menFaceCreams);
        $categories[] = $menFaceCreams;

        // Женская косметика
        $womenCare = new Category();
        $womenCare->setName('Женская косметика')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($rootCategory);
        $entityManager->persist($womenCare);

        $makeup = new Category();
        $makeup->setName('Макияж')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($womenCare);
        $entityManager->persist($makeup);
        $categories[] = $makeup;

        $hairCare = new Category();
        $hairCare->setName('Уход за волосами')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE)
            ->setParent($womenCare);
        $entityManager->persist($hairCare);
        $categories[] = $hairCare;

        // 3️⃣ Сохраняем категории
        $entityManager->flush();

        // 4️⃣ Товары
        for ($i = 1; $i <= 30; $i++) {
            $item = new Item();
            $item->setName('Товар #' . $i)
                ->setSku('SKU-' . $i)
                ->setPublishState(\App\Enum\ItemPublishStateEnum::ACTIVE);

            // Привязываем к случайной категории из списка "нижних" категорий
            $randomCategory = $categories[array_rand($categories)];
            $item->addCategory($randomCategory);

            // ВНИМАНИЕ:
            // Для твоего Listener → при генерации URL вида /catalog/.../item тебе нужно будет в Listener брать "основную категорию"
            // Это сейчас сделано по первой категории (addCategory)

            $entityManager->persist($item);
        }

        // 5️⃣ Сохраняем товары
        $entityManager->flush();

        return new Response('Категории (10) и товары (30) успешно созданы!');
    }

}
