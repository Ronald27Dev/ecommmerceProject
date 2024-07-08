#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>

#include "metodos_de_ordenacao.h"

int main () {

    int rodaCodigo = 0;
    int *vet, tam, opt, trocas = 0;
    float tempoTotal;
    clock_t inicio;
    clock_t fim;

    do {
        
        printf("Digite o tamanho do vetor:\n");
        do {
        
            if (scanf("%d", &tam) != 1 || tam < 1) {
                printf("Digite um numero maior que 0:\n");            
                while (getchar() != '\n');
                tam = 0;
            }
        } while (tam < 1);

        vet = malloc(tam * sizeof(int));
        if (vet == NULL) {
            printf("Falha ao alocar memoria.\n");
            return 1;
        }

        do {
            
            printf("Selecione uma das forma de criacao dos valores do vetor.\n");
            printf("1 - Crescente.\n");
            printf("2 - Decrescente.\n");
            printf("3 - Aleatoria.\n");

            if (scanf("%d", &opt) != 1 || opt < 1 || opt > 3) {
                printf("Opcao Invalida, Tente Novamente.\n");
                while (getchar() != '\n');
            }
        } while (opt > 3 || opt < 1);
        
        switch (opt) {

            case 1:
                geraValoresCrescente(vet, tam);
            break;
            
            case 2:
                geraValoresDecrescente(vet, tam);
            break;
            
            case 3:
                geraValoresAleatorios(vet, tam);
            break;
            
            default:
            break;
        }

        do {
            
            printf("Selecione uma das forma de ordenacao.\n\n");
            printf("1 - Selection Sort.\n");
            printf("2 - Insertion Sort.\n");
            printf("3 - Bubble Sort.\n");
            printf("4 - Shell Sort.\n");
            printf("5 - Merge Sort.\n");
            printf("6 - Heap Sort.\n");
            printf("7 - Radix Sort.\n");
            printf("8 - Quick Sort.\n");

            if (scanf("%d", &opt) != 1 || opt < 1 || opt > 8) {
                printf("Opcao Invalida, Tente Novamente.\n");
                while (getchar() != '\n');
            }
        } while (opt > 8 || opt < 1);


        switch (opt) {

            case 1:
                printf("\n-------Ordenando por Selection Sort-------\n\n");
                inicio = clock();
                selectionSort(vet, tam, &trocas);
                fim = clock();
            break;
            
            case 2:
                printf("\n-------Ordenando por Insertion Sort-------\n\n");
                inicio = clock();
                insertionSort(vet, tam, &trocas);
                fim = clock();
            break;
            
            case 3:
                printf("\n-------Ordenando por Bubble Sort-------\n\n");
                inicio = clock();
                bubbleSort(vet, tam, &trocas);
                fim = clock();
            break;
            
            case 4:
                printf("\n-------Ordenando por Shell Sort-------\n\n");
                inicio = clock();
                shellSort(vet, tam, &trocas);
                fim = clock();
            break;

            case 5:
                printf("\n-------Ordenando por Merge Sort-------\n\n");
                inicio = clock();
                mergeSort(vet, 0, tam-1, &trocas);
                fim = clock();
            break;

            case 6:
                printf("\n-------Ordenando por Heap Sort-------\n\n");
                inicio = clock();
                heapSort(vet, tam, &trocas);
                fim = clock();
            break;

            case 7:
                printf("\n-------Ordenando por Radix Sort-------\n\n");
                inicio = clock();
                radixSort(vet, tam, &trocas);
                fim = clock();
            break;

            case 8:
                printf("\n-------Ordenando por Quick Sort-------\n\n");
                inicio = clock();
                quickSort(vet, 0, tam-1, &trocas);
                fim = clock();
            break;

            default:
            break;
        }

        tempoTotal = (float) (fim - inicio) / CLOCKS_PER_SEC;

        printf("Digite 1 se deseja ver o vetor final\n");
        
        if (scanf("%d", &opt) != 1 || opt != 1) {
            while (getchar() != '\n');
        } else {
            printf("Vetor Final:\n");
            printVetor(vet, tam);
        }
        
        free(vet);
        printf("\nTempo total: %f segundos", tempoTotal);
        printf("\nTotal de trocas: %d\n", trocas);
    
        printf("Digite 1 se deseja ordenar por outro metodo?\n");
        if(scanf("%d", &rodaCodigo) != 1) {
            while (getchar() != '\n');
        }

    } while(rodaCodigo == 1);

    return 0;
}