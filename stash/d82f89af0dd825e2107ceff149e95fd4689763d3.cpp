#include <cv.h>
#include <cvaux.h>
#include <highgui.h>

using namespace cv;
using namespace std;

int main(int argc, char *argv[])
{
	char win[] = "IMAGE";
	Mat img(200, 300, CV_8UC3, Scalar(0, 0, 255));
	for (int i = 0; i < img.rows; i++) {
		for (int j = 0; j < img.cols; j++) {
			img.at<Vec3b>(i, j)[0] = 0;
			img.at<Vec3b>(i, j)[1] = 0;
			img.at<Vec3b>(i, j)[2] = 0;
			if (i < img.rows / 3) {
				img.at<Vec3b>(i, j)[2] = 255;
			} else if (i >= img.rows / 3 && i < 2 * img.rows / 3) {
				img.at<Vec3b>(i, j)[0] = 255;
			} else {
				img.at<Vec3b>(i, j)[1] = 255;
			}
		}
	}
	namedWindow(win, CV_WINDOW_AUTOSIZE);
	imshow(win, img);
	waitKey(0);
	return 0;
}